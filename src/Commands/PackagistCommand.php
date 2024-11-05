<?php

namespace JordanPartridge\Packagist\Commands;

use Illuminate\Console\Command;
use JordanPartridge\Packagist\Contracts\DataTransferObjectInterface;
use JordanPartridge\Packagist\Packagist;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Console\Command\Command as CommandAlias;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class PackagistCommand extends Command
{
    public $signature = 'packagist-client';

    public $description = 'Interactive Packagist client for package management';

    protected Packagist $packagist;

    protected array $availableActions = [];

    public function __construct(Packagist $packagist)
    {
        parent::__construct();
        $this->packagist = $packagist;
        $this->loadAvailableActions();
    }

    protected function loadAvailableActions(): void
    {
        $reflection = new ReflectionClass($this->packagist);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            // Skip magic methods and inherited methods from parent classes
            if ($method->getDeclaringClass()->getName() !== Packagist::class ||
                str_starts_with($method->getName(), '__')) {
                continue;
            }

            $methodName = $method->getName();
            $this->availableActions[$methodName] = $this->formatMethodName($methodName);
        }

        // Add built-in actions
        $this->availableActions['help'] = 'Help';
        $this->availableActions['exit'] = 'Exit';
    }

    protected function formatMethodName(string $name): string
    {
        // Convert camelCase to Title Case with spaces
        $formatted = preg_replace('/(?<!^)[A-Z]/', ' $0', $name);

        return ucfirst($formatted);
    }

    public function handle(): int
    {
        info('Welcome to the Packagist Client!');

        while (true) {
            $action = $this->getAction();

            if ($action === 'exit') {
                info('Thanks for using Packagist Client!');

                return CommandAlias::SUCCESS;
            }

            try {
                $this->executeAction($action);
            } catch (\Exception $e) {
                error("Error: {$e->getMessage()}");
            }
        }
    }

    protected function getAction(): string
    {
        return select(
            'What would you like to do?',
            $this->availableActions
        );
    }

    protected function executeAction(string $action): void
    {
        if ($action === 'help') {
            $this->showHelp();

            return;
        }

        if (! method_exists($this->packagist, $action)) {
            warning("Action '$action' not found.");

            return;
        }

        $parameters = $this->getMethodParameters($action);
        $result = $this->packagist->{$action}(...$parameters)->dto();
        $this->displayResult($action, $result);
    }

    protected function getMethodParameters(string $methodName): array
    {
        $reflection = new ReflectionMethod($this->packagist, $methodName);
        $parameters = [];

        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            $type = $param->getType() ?? 'mixed';

            // Format the prompt based on parameter name
            $prompt = ucfirst(str_replace('_', ' ', $paramName));

            // Get the parameter value based on type

            $value = text("Enter $prompt:");

            $parameters[] = $value;
        }

        return $parameters;
    }

    protected function displayResult(string $action, DataTransferObjectInterface $result): void
    {

        $actionName = $action;
        $this->info("Result for $actionName:");
        //show a menu of possible actions on the result
        $methods = get_class_methods($result);
        $menu = [];
        $methods = collect($methods)->reject(function ($method) {
            return in_array($method, ['toJson', 'fromJson', 'toArray', 'fromArray', '__construct']);
        });

        foreach ($methods as $method) {
            $menu[] = $method;

        }

        //display menu with prompts
        $selectedAction = select('What would you like to do?', $menu);
        $output = $result->{$selectedAction}();
        dd($output);
    }

    protected function isSingleDimensionalArray(array $array): bool
    {
        foreach ($array as $item) {
            if (is_array($item)) {
                return false;
            }
        }

        return true;
    }

    protected function displaySingleItem(array $item): void
    {
        $rows = [];
        foreach ($item as $key => $value) {
            $formattedValue = $this->formatValue($value);
            $rows[] = [ucfirst(str_replace('_', ' ', $key)), $formattedValue];
        }

        $this->table(['Property', 'Value'], $rows);
    }

    protected function displayList(array $items): void
    {
        if (empty($items)) {
            return;
        }

        // Get headers from the first item
        $firstItem = reset($items);
        $headers = array_map(function ($key) {
            return ucfirst(str_replace('_', ' ', $key));
        }, array_keys(is_array($firstItem) ? $firstItem : (array) $firstItem));

        // Format rows
        $rows = array_map(function ($item) {
            return array_map([$this, 'formatValue'], is_array($item) ? $item : (array) $item);
        }, $items);

        $this->table($headers, $rows);
    }

    protected function formatValue(mixed $value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map([$this, 'formatValue'], $value));
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_numeric($value) && $value > 1000) {
            return number_format($value);
        }

        return (string) $value;
    }

    /**
     * @throws \ReflectionException
     */
    protected function showHelp(): void
    {
        $this->info('Available Commands:');

        foreach ($this->availableActions as $action => $description) {
            if ($action === 'help' || $action === 'exit') {
                continue;
            }

            $method = new ReflectionMethod($this->packagist, $action);
            $parameters = $method->getParameters();
            $paramList = empty($parameters)
                ? 'No parameters required'
                : 'Parameters: '.implode(', ', array_map(fn ($p) => $p->getName(), $parameters));

            $this->line(sprintf(
                '• %s: %s',
                $description,
                $paramList
            ));
        }
    }
}
