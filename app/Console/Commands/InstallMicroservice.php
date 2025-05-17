<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallMicroservice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microservice:install {--serve : Serve the application after installation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure the attendance microservice';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting microservice installation...');


        $this->info('Setting up environment configuration...');
        $this->setupEnvironment();


        $this->info('Running database migrations...');
        $this->runMigrations();


        $this->info('Installing QR code package...');
        $this->installQrPackage();


        $this->info('Creating necessary directories...');
        $this->createDirectories();


        $this->info('Setting permissions...');
        $this->setPermissions();


        $this->info('Checking application key...');
        $this->checkAppKey();

        $this->info('Microservice installation completed successfully!');
        

        if ($this->option('serve')) {
            $this->serveApplication();
        } else {
            $this->info('You can now serve the application using: php artisan serve');
        }
        
        return 0;
    }

    /**
     * Setup environment configuration
     */
    private function setupEnvironment()
    {
        if (!File::exists(base_path('.env'))) {
            if (File::exists(base_path('.env.example'))) {
                File::copy(base_path('.env.example'), base_path('.env'));
                $this->info('.env file created from .env.example');
            } else {
                $this->error('.env.example file not found. Creating empty .env file.');
                File::put(base_path('.env'), '');
            }
        }
        $this->info('Please provide database configuration:');
        
        $dbConnection = $this->choice('Database connection type:', ['mysql', 'pgsql', 'sqlite', 'sqlsrv'], 0);
        $dbHost = $this->ask('Database host', '127.0.0.1');
        $dbPort = $this->ask('Database port', $dbConnection === 'mysql' ? '3306' : '5432');
        $dbName = $this->ask('Database name', 'attendance');
        $dbUser = $this->ask('Database username', 'root');
        $dbPassword = $this->secret('Database password');

        $this->updateEnvValue('DB_CONNECTION', $dbConnection);
        $this->updateEnvValue('DB_HOST', $dbHost);
        $this->updateEnvValue('DB_PORT', $dbPort);
        $this->updateEnvValue('DB_DATABASE', $dbName);
        $this->updateEnvValue('DB_USERNAME', $dbUser);
        $this->updateEnvValue('DB_PASSWORD', $dbPassword);


        $appName = $this->ask('Application name', 'Attendance System');
        $this->updateEnvValue('APP_NAME', '"'.$appName.'"');
        
        $appUrl = $this->ask('Application URL', 'http://localhost:8000');
        $this->updateEnvValue('APP_URL', $appUrl);
        

        $appEnv = $this->choice('Application environment:', ['local', 'production', 'testing'], 0);
        $this->updateEnvValue('APP_ENV', $appEnv);

        $appDebug = $this->confirm('Enable debug mode?', true);
        $this->updateEnvValue('APP_DEBUG', $appDebug ? 'true' : 'false');
    }

    /**
     * Update a value in the .env file
     */
    private function updateEnvValue($key, $value)
    {
        $envFile = base_path('.env');
        $envContents = File::get($envFile);
        if (preg_match("/^{$key}=.*/m", $envContents)) {
            $envContents = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContents);
        } else {

            $envContents .= "\n{$key}={$value}";
        }

        File::put($envFile, $envContents);
    }

    /**
     * Run database migrations
     */
    private function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $this->info('Database migrations completed successfully.');
        } catch (\Exception $e) {
            $this->error('Error running migrations: ' . $e->getMessage());
            $this->warn('Please check your database configuration in .env file.');
        }
    }

    /**
     * Install QR code package
     */
    private function installQrPackage()
    {
        try {
            if (!class_exists('BaconQrCode\Writer')) {
                $this->info('Installing bacon/bacon-qr-code package...');
                exec('composer require bacon/bacon-qr-code');
            } else {
                $this->info('QR code package is already installed.');
            }
        } catch (\Exception $e) {
            $this->error('Error installing QR code package: ' . $e->getMessage());
            $this->warn('You may need to install it manually: composer require bacon/bacon-qr-code');
        }
    }

    /**
     * Create necessary directories
     */
    private function createDirectories()
    {
        $directories = [
            storage_path('app/public/qrcodes'),
        ];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("Created directory: {$directory}");
            }
        }


        if (!file_exists(public_path('storage'))) {
            $this->info('Creating storage link...');
            Artisan::call('storage:link');
        }
    }

    /**
     * Set permissions for directories
     */
    private function setPermissions()
    {
        $paths = [
            storage_path(),
            storage_path('app'),
            storage_path('app/public'),
            storage_path('app/public/qrcodes'),
            storage_path('logs'),
            storage_path('framework'),
            base_path('bootstrap/cache'),
        ];

        foreach ($paths as $path) {
            if (File::exists($path)) {
                chmod($path, 0755);
            }
        }

        $this->info('Permissions set successfully.');
    }

    /**
     * Check and generate application key if needed
     */
    private function checkAppKey()
    {
        if (empty(env('APP_KEY'))) {
            $this->info('Generating application key...');
            Artisan::call('key:generate');
            $this->info('Application key generated successfully.');
        } else {
            $this->info('Application key already exists.');
        }
    }

    /**
     * Serve the application
     */
    private function serveApplication()
    {
        $this->info('Starting the development server...');
        $this->info('Application will be available at: http://localhost:8000');
        $this->info('Press Ctrl+C to stop the server');
        
        $this->comment('Server starting...');
        

        system('php artisan serve');
    }
}
