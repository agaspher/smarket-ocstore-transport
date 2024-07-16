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
use App\Import\ImporterFactory;
use App\Stats;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'import-products',
    description: 'Run import products from json to DB',
    aliases: ['i-p'],
)]
class ImportCommand extends Command
{
    public function __construct(
        private EntityManager $em
    ) {
        parent::__construct();
    }

    protected function configure()
    {
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

        if ($input->getOption('clear-all') === null) {
            return $this->clearDatabase($io);
        }

        $io->title('Import started.');

        $mapping = [
//            Product::class => Config::PRODUCT_FILE,
//            Category::class => Config::CATEGORY_FILE,
//            Option::class => Config::SIZE_FILE,
//            Product::class => 'cards615.json',
//            Product::class => 'cards2.json',
            Product::class => 'cards20k.json',
            Category::class => 'classif.json',
            Option::class => 'sizes.json',
//            Option::class => 'sizes2.json',
        ];

        $factory = new ImporterFactory();
        foreach ($mapping as $target => $source) {
            $importer = $factory->createImporter($source, $target, $this->em);

            $stats = $importer->import($io);
            $this->renderStatAsTable($stats, $io);
        }

//        $importer->deactivateEmptyCategories();

//        /** @var ProductRepository $repo */
//        $repo = $this->em->getRepository(Product::class)->getProductsBySku(['05158']);
//
//        var_dump($repo);

//        $this->renderStatAsTable($stats, $io);
        $io->title("Import finished.");

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
            ['Products created', $stats->getProductsCountCreated()],
            ['Products updated', $stats->getProductsCountUpdated()],
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

        $io->title('Start cleaning database.');
        foreach ($entities as $class => $msg) {
            $io->writeln(sprintf('Deleting [%s]', $msg));

            $qb = $this->em->getRepository($class)?->createQueryBuilder('t');
            $qb->delete()->getQuery()->execute();
        }
        $io->title('Database is cleared.');

        return 1;
    }
}
