<?php

namespace App\Command;

use Symfony\Bundle\MakerBundle\Util\ClassDetails;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use Symfony\Bundle\MakerBundle\Generator;

#[AsCommand(
    name: 'make:gts',
    description: 'Add a short description for your command',
)]
class MakeGtsCommand extends Command
{

    const DEFAULT_APP_BUNDLE = 'App';

    private ?string $app_bundle = null;

    private ?QuestionHelper $question_helper = null;

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('app_bundle', null, InputOption::VALUE_REQUIRED, 'Set different app bundle namespace prefix. For example: --app_bundle=MyAppBundle', self::DEFAULT_APP_BUNDLE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->question_helper = $this->getHelper('question');

        $this->app_bundle = $input->getOption('app_bundle');

        $app_bundle = $this->app_bundle . '\\';

        $namespace = $io->ask('Class namespace:', $app_bundle . '...');

        $namespace = self::clearDuplicatedTrailingSlashes($app_bundle . '\\' . $namespace . '\\');

        $class_name = $io->ask('Class name:', $namespace . '...');

        $full_class_namespace = self::clearDuplicatedTrailingSlashes($namespace . '\\' . $class_name);

        $class_path = $this->getPathOfClass($full_class_namespace);

        return Command::SUCCESS;
    }

    private function getPathOfClass(string $class): string
    {
        return (new ClassDetails($class))->getPath();
    }

    private static function clearDuplicatedTrailingSlashes(string $str):string
    {
        return str_replace('\\\\', '\\', $str);
    }
}
