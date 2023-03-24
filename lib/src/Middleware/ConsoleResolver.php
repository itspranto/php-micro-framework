<?php

namespace Pranto\Middleware;

class ConsoleResolver
{
    public static function resolve($commands)
    {
        $commands = [...static::predefinedCommands(), ...$commands];
        $_args = trim(implode(' ', array_splice($_SERVER['argv'], 1)));
        $res = 'No command found!';

        if (!$_args) {
            $res = "\n\n\t " . cmd_color("--- Availabe Commands --- ", 'green') . "\n\n";

            foreach ($commands as $cmd) {
                $res .= "  " . cmd_color($cmd['command'], 'green', '') . "\t\t${cmd['helpText']}\n\n";
            }
        } else {
            if ($commands) {
                foreach ($commands as $command) {
                    $cmd = explode("--", $_args);
    
                    if ($command['command'] == trim($cmd[0])) {
                        //preg_match_all("#--(?P<arg>[^=]+)=?(?P<val>[^-]+)$#", $_args, $opt);
                        $cmd = array_splice($cmd, 1);
                        $opt = [];
    
                        foreach ($cmd as $cm) {
                            $cm = explode("=", $cm);
                            $opt[trim($cm[0])] = trim($cm[1]);
                        }
    
                        $res = $command['func']($opt);
    
                        break;
                    }
                }
            }
        }

        print($res . PHP_EOL);
    }

    public static function predefinedCommands()
    {
        return [
            add_command('serve', 'Pranto\Console\BasicCommands::runServer', 
            'Runs a php development server, arguments (host, port)'),
            add_command('makemigrations', 'Pranto\Console\BasicCommands::makeMigrations',
            'Make migrations for all models within the src/Models Directory'),
            add_command('migrate', 'Pranto\Console\BasicCommands::migrate',
            'Migrate to database'),
            add_command('clear', 'Pranto\Console\BasicCommands::clear_cache',
            'Clear cache'),
        ];
    }
}