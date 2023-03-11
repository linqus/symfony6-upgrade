<?php

namespace App\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:random-spell', description: 'Cast a random spell!')]
class RandomSpellCommand extends Command
{
    protected static $defaultName;

    protected static $defaultDescription;

    public function __construct(private LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('your-name', InputArgument::OPTIONAL, 'Your name')
            ->addOption('yell', null, InputOption::VALUE_NONE, 'Yell?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $yourName = $input->getArgument('your-name');

        if ($yourName) {
            $io->note(sprintf('Hi %s!', $yourName));
        }

        $spells = [
            'alohomora',
            'confundo',
            'engorgio',
            'expecto patronum',
            'expelliarmus',
            'impedimenta',
            'reparo',
        ];

        $spell = $spells[array_rand($spells)];

        if ($input->getOption('yell')) {
            $spell = strtoupper($spell);
        }

        $this->logger->info('Casting spell: '.$spell);

        $io->success($spell);

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
