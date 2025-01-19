<?php

namespace App\Console\Commands;

use File;
use Illuminate\Console\Command;
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
            label: 'Model name',
            required: true,
        );

        $modelName = Str::studly($modelName); // Ensure proper naming convention
        $modelPath = app_path("Models/{$modelName}.php");
        $migrationName = "create_" . Str::snake(Str::plural($modelName)) . "_table";

        $this->generateModel($modelName, $modelPath);
        $this->call('make:migration', ['name' => $migrationName]);

        $filterName = "{$modelName}Filter";

        $repositoryIterfaceName = "{$modelName}RepositoryInterface";
        $repositoryName = "{$modelName}Repository";
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
}
