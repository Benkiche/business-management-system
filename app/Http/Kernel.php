'admin' => \App\Http\Middleware\AdminMiddleware::class,
protected $middleware = [
    // ... existing middleware
];

protected $middlewareGroups = [
    'web' => [
        // ... existing middleware
        \App\Http\Middleware\LogAuditActivity::class,  // Add this
    ],
];