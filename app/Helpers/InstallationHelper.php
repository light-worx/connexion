<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

trait InstallationHelper
{
    /**
     * Check if the application is currently being installed
     */
    protected function isInstalling(): bool
    {
        // Check if we're on the installer routes
        if (request()->is('install') || request()->is('install/*')) {
            return true;
        }
        
        // Check if we're on setup routes
        if (request()->is('setup.php') || request()->is('setup')) {
            return true;
        }

        // Check if installation has been completed
        if (!file_exists(storage_path('installed'))) {
            return true;
        }

        return false;
    }

    /**
     * Check if installation is complete
     */
    protected function isInstalled(): bool
    {
        return file_exists(storage_path('installed'));
    }

    /**
     * Check if we can safely access the database
     */
    protected function canAccessDatabase(): bool
    {
        // Don't try to access database during installation
        if ($this->isInstalling()) {
            return false;
        }

        try {
            // Check if database connection is configured
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            // For SQLite, check if the database file exists
            if ($driver === 'sqlite') {
                $database = config("database.connections.{$connection}.database");
                
                // Skip in-memory database
                if ($database === ':memory:') {
                    return false;
                }
                
                // Check if file exists and is not empty
                if (!file_exists($database) || filesize($database) === 0) {
                    return false;
                }
            }

            // Check if PDO driver is available for the connection type
            $pdoDrivers = \PDO::getAvailableDrivers();
            if (!in_array($driver, $pdoDrivers)) {
                return false;
            }

            // Try to establish connection
            DB::connection()->getPdo();
            
            return true;
        } catch (\Exception $e) {
            \Log::debug('Database not accessible: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Safely check if a database table exists
     */
    protected function tableExists(string $tableName): bool
    {
        if (!$this->canAccessDatabase()) {
            return false;
        }

        try {
            return \Schema::hasTable($tableName);
        } catch (\Exception $e) {
            \Log::debug("Table check failed for {$tableName}: " . $e->getMessage());
            return false;
        }
    }
}