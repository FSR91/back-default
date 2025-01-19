<?php

namespace App\Console\Commands;

use File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Str;

use function Laravel\Prompts\text;

class ModelWithMigrationFilterAndRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelName = text(
            label: 'Nome do Model:',
            required: true,
        );

        $modelName = Str::studly($modelName); // Ensure proper naming convention
        $modelPath = app_path("Models/{$modelName}.php");
        $migrationName = "create_" . Str::snake(Str::plural($modelName)) . "_table";

        $this->generateModel($modelName, $modelPath);
        // $this->call('make:migration', ['name' => $migrationName]);
        $this->generateMigration($migrationName);
        $this->generateFilter($modelName);
        $this->generateInterfaceRepository($modelName);
        $this->generateRepository($modelName);
    }



    /**
     * Gerar o model usando o stub.
     */
    protected function generateModel(string $modelName, string $modelPath)
    {
        // Caminho do stub
        $stubPath = base_path('stubs\model.stub');

        if (!File::exists($stubPath)) {
            $this->error("O stub do model não foi encontrado em {$stubPath}.");
            return;
        }

        // Ler o conteúdo do stub
        $stubContent = File::get($stubPath);

        // Substituir placeholders no stub
        $content = str_replace(
            ['{{ class }}'],
            [$modelName],
            $stubContent
        );

        // Criar diretório, se necessário
        File::ensureDirectoryExists(dirname($modelPath));

        // Salvar o arquivo do model
        File::put($modelPath, $content);

        $this->info("Model {$modelName} criado com sucesso em {$modelPath}.");
    }

    protected function generateInterfaceRepository(string $modelName)
    {

        // Caminho do stub
        $stubPath = base_path('stubs\repositoryInterface.stub');
        $interfacePath = app_path("Contracts/{$modelName}RepositoryInterface.php");


        if (!File::exists($stubPath)) {
            $this->error("O stub do model não foi encontrado em {$stubPath}.");
            return;
        }

        // Ler o conteúdo do stub        
        $stubContent = File::get($stubPath);

        // Substituir placeholders no stub
        $content = str_replace(
            ['{{ modelName }}'],
            [$modelName],
            $stubContent
        );

        // Criar diretório, se necessário
        File::ensureDirectoryExists(dirname($interfacePath));

        // Salvar o arquivo do model
        File::put($interfacePath, $content);

        $this->info("Interface do Model {$modelName} criado com sucesso em {$interfacePath}.");
    }

    protected function generateRepository(string $modelName)
    {

        // Caminho do stub
        $stubPath = base_path('stubs\repository.stub');
        $repositoryPath = app_path("Infra\Eloquent/{$modelName}Repository.php");
        $modelInstance = Str::camel($modelName);


        if (!File::exists($stubPath)) {
            $this->error("O stub do model não foi encontrado em {$stubPath}.");
            return;
        }

        // Ler o conteúdo do stub        
        $stubContent = File::get($stubPath);

        // Substituir placeholders no stub
        $content = str_replace(
            ['{{ modelName }}', '{{ modelInstance }}'],
            [$modelName, $modelInstance],
            $stubContent
        );

        // Criar diretório, se necessário
        File::ensureDirectoryExists(dirname($repositoryPath));

        // Salvar o arquivo do model
        File::put($repositoryPath, $content);

        $this->info("Repository do Model {$modelName} criado com sucesso em {$repositoryPath}.");
    }


    protected function generateFilter(string $modelName)
    {

        // Caminho do stub
        $stubPath = base_path('stubs\filter.stub');
        $filterPath = app_path("Infra\Eloquent\Filter\\{$modelName}Filter.php");
        $modelInstance = Str::camel($modelName);


        if (!File::exists($stubPath)) {
            $this->error("O stub do model não foi encontrado em {$stubPath}.");
            return;
        }

        // Ler o conteúdo do stub        
        $stubContent = File::get($stubPath);

        // Substituir placeholders no stub
        $content = str_replace(
            ['{{ modelName }}'],
            [$modelName],
            $stubContent
        );

        // Criar diretório, se necessário
        File::ensureDirectoryExists(dirname($filterPath));

        // Salvar o arquivo do model
        File::put($filterPath, $content);

        $this->info("Filter do model {$modelName} criado com sucesso em {$filterPath}.");
    }

    protected function generateMigration(string $migrationName)
    {
        // Caminho base para as migrations
        $migrationsPath = database_path('migrations');

        // Lista os arquivos antes de criar a migration
        $beforeFiles = File::allFiles($migrationsPath);

        // Executa o comando sem mostrar a saída
        Artisan::call('make:migration', ['name' => $migrationName]);

        // Lista os arquivos depois de criar a migration
        $afterFiles = File::allFiles($migrationsPath);

        // Identifica o novo arquivo gerado
        $newFiles = array_diff(
            array_map('strval', $afterFiles),
            array_map('strval', $beforeFiles)
        );

        if (!empty($newFiles)) {
            $newMigrationPath = reset($newFiles);
            $this->info("Migration criada: $newMigrationPath");
        } else {
            $this->info("Nenhuma nova migration foi criada.");
        }
    }
}
