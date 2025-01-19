<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use function Laravel\Prompts\text;
use function Laravel\Prompts\multiselect;

class CreateControllerHandler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:controller {modelName?}';

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
        $modelName = $this->argument('modelName') ?? text(
            label: 'Nome do Model:',
            required: true,
        );

        $verbs = multiselect(
            label: 'Quais Verbos:',
            options: ['Index', 'Create', 'Show', 'Update', 'Delete'],
            required: true,
        );

        $modelName = Str::studly($modelName); // Convert to PascalCase
        $controllerPath = app_path("Http/Controllers/{$modelName}");
        $applicationPath = app_path("App/Application/{$modelName}");

        $this->generateResources($modelName);

        Artisan::call('make:resource', ['name' => "{$modelName}/{$modelName}Resource"]);
        $this->info("{$modelName}Resource criado em app\Http\Resources/{$modelName}Resource");
        Artisan::call('make:resource', ['name' => "{$modelName}/{$modelName}Collection"]);
        $this->info("{$modelName}Collection criado em app\Http\Resources/{$modelName}Collection");

        // Generate Controllers
        foreach ($verbs as $verb) {
            $this->generateHandler($verb, $modelName, $applicationPath);
            $this->generateCommand($verb, $modelName, $applicationPath);
            $this->generateController($verb, $modelName, $controllerPath);
        }

        $this->info('Controllers, Handlers, Commands e Resouces criados com sucesso!');
    }

    /**
     * Generate a specific controller using a stub.
     */
    protected function generateController(string $type, string $modelName, string $controllerPath)
    {
        if ($type === 'Index') {
            $stubPath = base_path("stubs/controllers/index.stub");
        } else {
            $stubPath = base_path("stubs/controllers/others.stub");
        }

        $filePath = "{$controllerPath}/{$type}Controller.php";

        $this->generateFile($stubPath, $filePath, [
            '{{ modelName }}' => $modelName,
            '{{ type }}' => $type,
            '{{ resourceNamespace }}' => "App\\Http\\Resources\\" . Str::lower($modelName),
            '{{ resourceName }}' => "{$modelName}Resource",
            '{{ collectionName }}' => "{$modelName}Collection",
        ]);

        $this->info("Controller {$type} criado com sucesso em {$controllerPath}!");
    }

    /**
     * Generate a shared handler for all operations using a stub.
     */
    protected function generateHandler(string $type, string $modelName, string $handlerPath)
    {
        if ($type === 'Index') {
            $stubPath = base_path("stubs/handlers/index.stub");
        } else {
            $stubPath = base_path("stubs/handlers/others.stub");
        }

        $filePath = "{$handlerPath}/{$type}/Handler.php";

        if (!File::exists($filePath)) {
            $this->generateFile($stubPath, $filePath, [
                '{{ modelName }}' => $modelName,
            ]);

            $this->info("Handler {$type} criado com sucesso: {$filePath}");
        } else {
            $this->info("Handler {$type} já existe: {$filePath}");
        }
    }

    /**
     * Generate a specific command using a stub.
     */
    protected function generateCommand(string $type, string $modelName, string $commandPath)
    {
        if ($type === 'Index') {
            $stubPath = base_path("stubs/commands/index.stub");
        } else {
            $stubPath = base_path("stubs/commands/others.stub");
        }
        $filePath = "{$commandPath}/{$type}/Command.php";

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
  
    protected function generateResources(string $modelName)
{
    $resourcePath = app_path("Http/Resources/" . strtolower($modelName));

   
    $resourceStubPath = base_path("stubs/resources/resource.stub");
    $resourceFilePath = "{$resourcePath}/{$modelName}Resource.php";

    $this->generateFile($resourceStubPath, $resourceFilePath, [
        '{{ modelName }}' => $modelName,
        '{{ namespace }}' => "App\\Http\\Resources\\" . Str::lower($modelName),
    ]);

    $this->info("Resource {$modelName}Resource criado com sucesso em {$resourceFilePath}!");

   
    $collectionStubPath = base_path("stubs/resources/collection.stub");
    $collectionFilePath = "{$resourcePath}/{$modelName}Collection.php";

    $this->generateFile($collectionStubPath, $collectionFilePath, [
        '{{ modelName }}' => $modelName,
        '{{ namespace }}' => "App\\Http\\Resources\\" . Str::lower($modelName),
    ]);

    $this->info("Collection {$modelName}Collection criado com sucesso em {$collectionFilePath}!");
}
}
