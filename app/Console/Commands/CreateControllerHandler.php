<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use function Laravel\Prompts\text;

class CreateControllerHandler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:controller-handler {modelName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate controllers, handlers, and DTO commands for a given model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelName = $this->argument('modelName');

        $modelName = Str::studly($modelName); // Convert to PascalCase
        $controllerPath = app_path("Http/Controllers/{$modelName}");
        $handlerPath = app_path("Handlers/{$modelName}");
        $commandPath = app_path("Commands/{$modelName}");

        // Generate Controllers
        $this->generateController('Index', $modelName, $controllerPath);
        $this->generateController('Create', $modelName, $controllerPath);
        $this->generateController('Show', $modelName, $controllerPath);
        $this->generateController('Update', $modelName, $controllerPath);

        // Generate a shared Handler (used for all operations)
        $this->generateSharedHandler($modelName, $handlerPath);

        // Generate Commands (DTO structure)
        $this->generateCommand('Index', $modelName, $commandPath);
        $this->generateCommand('Create', $modelName, $commandPath);
        $this->generateCommand('Show', $modelName, $commandPath);
        $this->generateCommand('Update', $modelName, $commandPath);

        $this->info('Controllers, Handlers, and DTO Commands created successfully!');
    }

    /**
     * Generate a specific controller using a stub.
     */
    protected function generateController(string $type, string $modelName, string $controllerPath)
    {
        $stubPath = base_path("stubs/controller.stub");
        $filePath = "{$controllerPath}/{$type}Controller.php";

        $this->generateFile($stubPath, $filePath, [
            '{{ modelName }}' => $modelName,
            '{{ type }}' => $type,
        ]);
    }

    /**
     * Generate a shared handler for all operations using a stub.
     */
    protected function generateSharedHandler(string $modelName, string $handlerPath)
    {
        $filePath = "{$handlerPath}/Handler.php";

        if (!File::exists($filePath)) {
            $stubPath = base_path("stubs/handler.stub");
            $this->generateFile($stubPath, $filePath, [
                '{{ modelName }}' => $modelName,
            ]);

            $this->info("Shared Handler created: {$filePath}");
        } else {
            $this->info("Shared Handler already exists: {$filePath}");
        }
    }

    /**
     * Generate a specific command using a stub.
     */
    protected function generateCommand(string $type, string $modelName, string $commandPath)
    {
        $stubPath = base_path("stubs/command.stub");
        $filePath = "{$commandPath}/{$type}Command.php";

        $this->generateFile($stubPath, $filePath, [
            '{{ modelName }}' => $modelName,
            '{{ type }}' => $type,
        ]);
    }

    /**
     * Generate a file using a stub and replace placeholders.
     */
    protected function generateFile(string $stubPath, string $filePath, array $placeholders)
    {
        if (!File::exists($stubPath)) {
            $this->error("The stub file was not found in {$stubPath}.");
            return;
        }

        $stubContent = File::get($stubPath);

        foreach ($placeholders as $placeholder => $value) {
            $stubContent = str_replace($placeholder, $value, $stubContent);
        }

        File::ensureDirectoryExists(dirname($filePath));
        File::put($filePath, $stubContent);

        $this->info("File created: {$filePath}");
    }
}
