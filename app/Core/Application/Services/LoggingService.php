<?php

namespace App\Core\Application\Services;

use Illuminate\Support\Facades\Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class LoggingService
{
    private const LOG_CHANNELS = [
        'subscription' => 'logs/subscription.log',
        'booking' => 'logs/booking.log',
        'cart' => 'logs/cart.log',
        'payment' => 'logs/payment.log',
        'auth' => 'logs/auth.log',
        'api' => 'logs/api.log',
        'security' => 'logs/security.log',
        'performance' => 'logs/performance.log',
    ];

    /**
     * Log subscription-related events.
     */
    public function logSubscription(string $level, string $message, array $context = []): void
    {
        $this->logToChannel('subscription', $level, $message, $context);
    }

    /**
     * Log booking-related events.
     */
    public function logBooking(string $level, string $message, array $context = []): void
    {
        $this->logToChannel('booking', $level, $message, $context);
    }

    /**
     * Log cart-related events.
     */
    public function logCart(string $level, string $message, array $context = []): void
    {
        $this->logToChannel('cart', $level, $message, $context);
    }

    /**
     * Log payment-related events.
     */
    public function logPayment(string $level, string $message, array $context = []): void
    {
        $this->logToChannel('payment', $level, $message, $context);
    }

    /**
     * Log authentication-related events.
     */
    public function logAuth(string $level, string $message, array $context = []): void
    {
        $this->logToChannel('auth', $level, $message, $context);
    }

    /**
     * Log to a specific channel.
     */
    private function logToChannel(string $channel, string $level, string $message, array $context = []): void
    {
        $logger = $this->getChannelLogger($channel);
        
        match ($level) {
            'debug' => $logger->debug($message, $context),
            'info' => $logger->info($message, $context),
            'warning' => $logger->warning($message, $context),
            'error' => $logger->error($message, $context),
            'critical' => $logger->critical($message, $context),
            default => $logger->info($message, $context),
        };
    }

    /**
     * Get a logger instance for a specific channel.
     */
    private function getChannelLogger(string $channel): Logger
    {
        $logger = new Logger($channel);
        
        // Use default log path if channel not found
        $logPath = self::LOG_CHANNELS[$channel] ?? 'logs/laravel.log';
        
        $handler = new RotatingFileHandler(
            storage_path($logPath),
            30, // Keep 30 days of logs
            Logger::INFO
        );
        
        $logger->pushHandler($handler);
        
        return $logger;
    }

    /**
     * Log API request/response.
     */
    public function logApiRequest(array $request, array $response = [], float $duration = 0): void
    {
        $context = [
            'method' => $request['method'] ?? 'unknown',
            'url' => $request['url'] ?? 'unknown',
            'ip' => $request['ip'] ?? 'unknown',
            'user_agent' => $request['user_agent'] ?? 'unknown',
            'user_id' => $request['user_id'] ?? null,
            'response_status' => $response['status'] ?? null,
            'duration_ms' => round($duration * 1000, 2),
        ];

        $this->logToChannel('api', 'info', 'API Request', $context);
    }

    /**
     * Log security events.
     */
    public function logSecurity(string $event, array $context = []): void
    {
        $this->logToChannel('security', 'warning', $event, $context);
    }

    /**
     * Log performance metrics.
     */
    public function logPerformance(string $action, float $duration, array $context = []): void
    {
        $context = array_merge($context, [
            'action' => $action,
            'duration_ms' => round($duration * 1000, 2),
        ]);

        $this->logToChannel('performance', 'info', 'Performance Metric', $context);
    }

    /**
     * Get log statistics.
     */
    public function getLogStats(): array
    {
        $stats = [];
        
        foreach (self::LOG_CHANNELS as $channel => $file) {
            $logFile = storage_path($file);
            
            if (file_exists($logFile)) {
                $stats[$channel] = [
                    'file_size' => filesize($logFile),
                    'last_modified' => filemtime($logFile),
                    'file_path' => $logFile,
                ];
            } else {
                $stats[$channel] = [
                    'file_size' => 0,
                    'last_modified' => null,
                    'file_path' => $logFile,
                ];
            }
        }
        
        return $stats;
    }
}
