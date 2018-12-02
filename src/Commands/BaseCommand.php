<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 8:22 AM
 */

namespace Kibb\Backup\Commands;

use Illuminate\Console\Command;
use Kibb\Backup\Helpers\ConsoleOutPut;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{

    public function run(InputInterface $input, OutputInterface $output)
    {
        app(ConsoleOutPut::class)->setOutput($this);
        return parent::run($input, $output); // TODO: Change the autogenerated stub
    }
}