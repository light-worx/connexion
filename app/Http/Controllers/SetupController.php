<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetupController extends Controller
{
    public function index()
    {
        // Prevent access if already installed
        if (file_exists(storage_path('installed.txt'))) {
            return redirect('/admin');
        }
        return view('setup'); // We will create this blade file
    }

    public function configure(Request $request)
    {
        // ... validation code ...

        $dbData = [
            'DB_HOST'     => $request->db_host,
            'DB_DATABASE' => $request->db_name,
            'DB_USERNAME' => $request->db_user,
            'DB_PASSWORD' => $request->db_pass,
            'DB_CONNECTION' => 'mysql', // Ensure it switches from sqlite
        ];

        // 1. Force the current process to use these values
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $dbData['DB_HOST'],
            'database.connections.mysql.database' => $dbData['DB_DATABASE'],
            'database.connections.mysql.username' => $dbData['DB_USERNAME'],
            'database.connections.mysql.password' => $dbData['DB_PASSWORD'],
        ]);

        // 2. Wipe any existing connection instances
        \Illuminate\Support\Facades\DB::purge('mysql');
        \Illuminate\Support\Facades\DB::reconnect('mysql');

        try {
            // 3. Run Migrations (This now uses the hot-reloaded config)
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

            // 4. Update the actual .env file for FUTURE requests
            $this->updateEnv($dbData);

            // 5. Create the Admin (Now writing to MySQL)
            \App\Models\User::updateOrCreate(
                ['email' => $request->admin_email],
                [
                    'name' => 'Admin',
                    'password' => bcrypt($request->admin_pass),
                    'email_verified_at' => now(),
                ]
            );

            file_put_contents(storage_path('installed.txt'), now());
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Migration failed: ' . $e->getMessage()], 500);
        }
    }

    protected function updateEnv(array $data)
    {
        $envPath = base_path('.env');
        
        // Ensure the file exists and is writable
        if (!file_exists($envPath)) {
            copy(base_path('.env.example'), $envPath);
        }

        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            // This regex handles existing keys and ensures values are updated
            $pattern = "/^{$key}=.*/m";
            $entry = "{$key}=\"{$value}\"";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $entry, $content);
            } else {
                $content .= "\n" . $entry;
            }
        }

        file_put_contents($envPath, $content);
    }

    public function testConnection(Request $request)
    {
        $host = $request->input('db_host');
        $database = $request->input('db_name');
        $username = $request->input('db_user');
        $password = $request->input('db_pass');

        try {
            // Attempt a manual PDO connection
            $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 5, // Fast timeout
            ];
            
            new \PDO($dsn, $username, $password, $options);

            return response()->json(['status' => 'success', 'message' => 'Connection successful!']);
        } catch (\PDOException $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 400);
        }
    }
}