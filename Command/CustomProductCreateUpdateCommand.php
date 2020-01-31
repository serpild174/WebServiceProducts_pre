<?php

namespace WebServiceProducts\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


class CustomProductCreateUpdateCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'oro:custom-product-createupdate';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates new web service products.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create web service products...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs a message followed by a "\n"
        $output->writeln('Whoa!');

        // outputs a message without adding a "\n" at the end of the line
        $output->write('You are about to ');
        $output->write('create a user.');

        return 0;
    }
}
