<?php

namespace Pranto\Console;

class BasicCommands
{
    public static function runServer($opt)
    {
        $host = $opt['host'] ?? '127.0.0.1';
        $port = $opt['port'] ?? 9000;
        shell_exec("php -S $host:$port -t " . join_path(app()->path, "public") . " " . join_path(app()->path, 'server.php'));
    }

    public static function makeMigrations($opt)
    {
        $models = glob(join_path(app()->path, 'src', 'Models') . DIRECTORY_SEPARATOR . "*.php");
        $migrations = glob(join_path(app()->path, 'migrations') . DIRECTORY_SEPARATOR . "*.php");

        foreach ($migrations as $migration) {
            unlink($migration);
        }

        foreach ($models as $model) {
            $model = str_replace('.php', '', basename($model));
            $modelObject = "App\\Models\\" . $model;
            $model = strtolower($model);
            $columns = [];
            $f_keys = [];

            foreach ($modelObject::fields() as $field => $attr) {
                if ($attr['type'] == 'foreign_key') {
                    $schema = "\t{$field} INT";
                    if (!$attr['null']) {
                        $schema .= ' NOT';
                    }
                    $schema .= ' NULL';
                    $columns[] = $schema;

                    $foreignTable = $attr['foreignTo']::$table ?? str_replace('models\\', '', strtolower($attr['foreignTo'])) . "s";
                    $f_keys[] = "\n\tCONSTRAINT fk_{$field}\n\t\tFOREIGN KEY({$field})\n\t\t\tREFERENCES {$foreignTable}({$attr['foreignTo']::$primaryKey})\n\t\t\tON DELETE {$attr['onDelete']}";
                } elseif ($attr['type'] == 'many_to_many') {
                    $relatedTo = str_replace('models\\', '', strtolower($attr['relatedTo']));
                    $tableName = $attr['fieldName'] ? $attr['fieldName'] : $model . "_" . $relatedTo;
                    $m2m_up = "\nCREATE TABLE {$tableName} (id SERIAL PRIMARY KEY, {$model} INTEGER NOT NULL, {$relatedTo} INTEGER NOT NULL);";
                    $m2m_down = "DROP TABLE IF EXISTS {$tableName};";
                } else {
                    $schema = "\t{$field} {$attr['type']}";

                    if ($attr['max_length']) {
                        $schema .= "({$attr['max_length']})";
                    }

                    if (!$attr['null']) {
                        $schema .= ' NOT';
                    }
                    $schema .= ' NULL';

                    if ($attr['default']) {
                        $schema .= " DEFAULT ";
                        $schema .= is_string($attr['default']) ? "'{$attr['default']}'" : $attr['default'];
                    }

                    if ($attr['unique']) {
                        $schema .= " UNIQUE";
                    }

                    $columns[] = $schema;
                }
            }

            $file = join_path(app()->path, 'migrations', ($f_keys ? '' : '00_') . 'create_' . $model . '.php');

            $table = $modelObject::$table ?? "{$model}s";

            $f_keys = $f_keys ? implode(',' . PHP_EOL, $f_keys) : null;
            $columns = implode(',' . PHP_EOL, $columns);

            if ($f_keys) {
                $columns .= ",";
            }

            $columns = $modelObject::$primaryKey . " SERIAL PRIMARY KEY,\n" . $columns;

            $up = sprintf("CREATE TABLE {$table}(\n%s\n%s\r\n);",
                $columns,
                $f_keys
            );

            $down = "DROP TABLE IF EXISTS {$table};";

            if (isset($m2m_up)) {
                $up .= $m2m_up;
                $down .= $m2m_down;
            }

            $data = <<<PHP
<?php

/** Auto generated migration file */

\$up = "{$up}";

\$down = "{$down}";
PHP;

            file_put_contents($file, $data);
        }
    }

    public static function migrate($opt)
    {
        $migrations = glob(app()->path . "migrations" . DIRECTORY_SEPARATOR . "*.php");

        if (isset($opt['refresh'])) {
            foreach ($migrations as $migration) {
                include $migration;

                print(cmd_color("\nDropping: ", 'red', '') . cmd_color(basename($migration), 'cyan'));
                db()->pdo()->prepare($down)->execute();

            }
        }

        foreach ($migrations as $migration) {
            include $migration;
            
            print(cmd_color("\nMigrating: ", 'green', '') . cmd_color(basename($migration), 'cyan'));
            db()->pdo()->prepare($up)->execute();
        }
    }

    public static function clear_cache($opt)
    {
        $cache_dir = app()->config['cache_dir'];

        if (isset($opt['type'])) {
            foreach (glob(join_path($cache_dir, $opt['type'], "*")) as $file) {
                unlink($file);
            }
        } else {
            foreach (glob(join_path($cache_dir, 'models', "*")) as $file) {
                unlink($file);
            }
            foreach (glob(join_path($cache_dir, 'urls', "*")) as $file) {
                unlink($file);
            }
            foreach (glob(join_path($cache_dir, 'templates', "*")) as $file) {
                unlink($file);
            }
        }
    }
}