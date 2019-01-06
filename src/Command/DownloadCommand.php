<?php
namespace App\Command;

use App\Downloader\Services\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends Command
{
    protected static $defaultName = 'app:download';

    /** @var Factory */
    private $downloaderFactory;

    /**
     * @param Factory $downloaderFactory
     */
    public function __construct(Factory $downloaderFactory)
    {
        $this->downloaderFactory = $downloaderFactory;

        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this
            ->setDescription('Downloads a comic')
            ->addArgument('comic', InputArgument::REQUIRED, 'Comic name.')
            ->addOption('--boundary_start', '-s', InputOption::VALUE_OPTIONAL, 'Start downloads from this boundary.')
            ->addOption('--boundary_end', '-e', InputOption::VALUE_OPTIONAL, 'Download until this boundary.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $comic = $input->getArgument('comic');

        $downloader = $this->downloaderFactory->create($comic, $output);

        $downloader->setBoundaries(
            $input->getOption('boundary_start'),
            $input->getOption('boundary_end')
        );
        $downloader->process();
    }
}
