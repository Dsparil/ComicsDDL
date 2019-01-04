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
            ->addOption('--date_start', '-s', InputOption::VALUE_OPTIONAL, 'Start downloads from this date.')
            ->addOption('--date_end', '-e', InputOption::VALUE_OPTIONAL, 'Download until this date.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $comic = $input->getArgument('comic');

        $this->getDateBoundaries($input, $date_start, $date_end);
        $downloader = $this->downloaderFactory->create($comic, $output);

        $downloader->process($date_start, $date_end);
    }

    /**
     * @param InputInterface $input
     * @param null $date_start
     * @param null $date_end
     * @throws \LogicException
     */
    private function getDateBoundaries(InputInterface $input, &$date_start = null, &$date_end = null)
    {
        $opt_date_start = $input->getOption('date_start');
        $opt_date_end   = $input->getOption('date_end');

        $date_start = ($opt_date_start === null)? null : new \DateTime($opt_date_start);
        $date_end   = ($opt_date_end === null)?   null : new \DateTime($opt_date_end);

        if ($date_start > $date_end)
        {
            throw new \LogicException('End date cannot be greater than start date.');
        }
    }
}
