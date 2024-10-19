<?php

declare(strict_types=1);

namespace App\Command;

use App\Config\Config;
use App\Entity\Category;
use App\Entity\CategoryDescription;
use App\Entity\CategoryPath;
use App\Entity\CategoryStore;
use App\Entity\ExtraImage;
use App\Entity\Log;
use App\Entity\Option;
use App\Entity\OptionValue;
use App\Entity\OptionValueDescription;
use App\Entity\Product;
use App\Entity\ProductDescription;
use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use App\Entity\ProductToStore;
use App\Exception\AppException;
use App\Exception\AppExceptionInterface;
use App\Import\ImporterFactory;
use App\Stats;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportCommand extends Command
{
    protected static $defaultName = 'import-products';

    protected static $defaultDescription = 'Run import from json to DB';

    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setAliases(['i-p']);
        $this->addOption(
            'clear-all',
            null,
            InputOption::VALUE_OPTIONAL,
            'Clear all tables which are used in import.',
            false
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** if we are clearing database, we don't need to import */
        if ($input->getOption('clear-all') === null) {
            return $this->clearDatabase($io);
        }

        $io->title('Import starts.');

        $mapping = [
            Product::class => Config::$productFile,
            Category::class => Config::$categoryFile,
            Option::class => Config::$sizeFile,
        ];

        $stats = new Stats($this->em);
        $stats->removeOldLog();

        $factory = new ImporterFactory();
        foreach ($mapping as $target => $source) {
            $importer = $factory->createImporter($source, $target, $this->em);

            try {
                $stats = $importer->import($io);
                $this->renderStatAsTable($stats, $io);
            } catch (AppExceptionInterface $e) {
                $io->error($e->getMessage());
            }
        }

        $io->title("Import is finished.");

        return 1;
    }

    private function renderStatAsTable(Stats $stats, $io): void
    {
        $table = $io->createTable();
        $table->setStyle('box');
        $table->setHeaders(['metric', 'value']);
        $table->setRows([
            ['Duration, s', $stats->getDuration()],
            ['Used memory, Mb', $stats->getUsedMemory()],
            ['Rows founded', $stats->getRowsCountInFile()],
            [sprintf('[%s] created', $stats->getImportType()), $stats->getCountCreated()],
            [sprintf('[%s] updated', $stats->getImportType()), $stats->getCountUpdated()],
            ['Transaction count', $stats->getTransactionCount()],
        ]);

        $table->render();
    }

    private function clearDatabase(SymfonyStyle $io): int
    {
        if (!$io->ask(
            'Are you sure you want to clear database? (y/n)',
            'n',
            fn(string $response) => $response === 'y'
        )) {
            return Command::SUCCESS;
        }

        $entities = [
            Product::class => 'Products',
            ProductDescription::class => 'Descriptions for products',
            ExtraImage::class => 'Extra images for products',
            ProductToStore::class => 'Relations between product and store',
            Category::class => 'Categories',
            CategoryDescription::class => 'Descriptions for categories',
            CategoryPath::class => 'Paths for categories',
            CategoryStore::class => 'Relations between category and store',
            OptionValue::class => 'Available option values',
            OptionValueDescription::class => 'Description for option values',
            ProductOption::class => 'Relations between product and option',
            ProductOptionValue::class => 'Additional information for product-option relation',
            Log::class => 'Log records'
        ];

        $stats = new Stats($this->em);
        $stats->setImportType('clear');

        $io->title('Start cleaning database.');
        foreach ($entities as $class => $msg) {
            $msg = sprintf('Deleting [%s]', $msg);
            $io->writeln($msg);
            $stats->addError([$msg]);

            $qb = $this->em->getRepository($class);

            if ($qb) {
                $qb = $qb->createQueryBuilder('t');
                $qb->delete()->getQuery()->execute();
            }

        }
        $io->title('Database is cleared.');

        $stats->saveLog();

        return 1;
    }
}
