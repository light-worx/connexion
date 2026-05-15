<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tasks API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for your remote Tasks API.
    | Set TASKS_API_URL in your .env (no trailing slash).
    | Example: TASKS_API_URL=https://tasks.lightworx.co.za
    |
    */
    'base_url' => env('TASKS_API_URL', 'http://localhost:8000'),

    /*
    |--------------------------------------------------------------------------
    | Client Credentials
    |--------------------------------------------------------------------------
    |
    | The client ID and secret issued by the Tasks API for your application.
    | These are passed to the tasks-api-client SDK.
    |
    | Set in .env:
    |   TASKS_API_CLIENT_ID=cli_xxx
    |   TASKS_API_CLIENT_SECRET=xxx
    |
    */
    'client_id'     => env('TASKS_API_CLIENT_ID', ''),
    'client_secret' => env('TASKS_API_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Navigation Group
    |--------------------------------------------------------------------------
    |
    | The navigation group label shown in the Filament sidebar.
    |
    */
    'navigation_group' => env('TASKS_NAV_GROUP', 'Task Management'),

    /*
    |--------------------------------------------------------------------------
    | Navigation Sort Order
    |--------------------------------------------------------------------------
    */
    'navigation_sort' => 10,

    'assignee_model'       => env('TASKS_ASSIGNEE_MODEL', null),      // e.g. App\Models\Individual
    'assignee_label_field' => env('TASKS_ASSIGNEE_LABEL', 'name'),     // field shown in dropdown
    'assignee_email_field' => env('TASKS_ASSIGNEE_EMAIL', 'email'),    // field written to assigned_email
    'assignee_scope'       => null, // optional: closure to scope the query
];
