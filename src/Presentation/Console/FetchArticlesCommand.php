<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Application\Article\UseCase\FetchArticlesUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:articles:fetch',
    description: 'Fetch articles from GNews API and save to database',
)]
class FetchArticlesCommand extends Command
{
    private FetchArticlesUseCase $fetchArticlesUseCase;

    public function __construct(FetchArticlesUseCase $fetchArticlesUseCase)
    {
        parent::__construct();
        $this->fetchArticlesUseCase = $fetchArticlesUseCase;
    }

    protected function configure(): void
    {
        $this
            ->addOption('category', 'c', InputOption::VALUE_OPTIONAL, 'Article category (general, world, business, technology, etc.)')
            ->addOption('lang', 'l', InputOption::VALUE_OPTIONAL, 'Language code (en, ar, etc.)')
            ->addOption('max', 'm', InputOption::VALUE_OPTIONAL, 'Maximum number of articles to fetch', 10)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $category = $input->getOption('category');
        $lang = $input->getOption('lang');
        $max = (int) $input->getOption('max');

        $io->title('Fetching Articles from GNews API');
        $io->info(sprintf('Category: %s, Language: %s, Max: %d', $category, $lang, $max));

        try {
            $result = $this->fetchArticlesUseCase->execute($category, $lang, $max);

            $io->success('Articles fetched successfully!');
            $io->table(
                ['Metric', 'Count'],
                [
                    ['Pages fetched', $result['pages']],
                    ['Total fetched', $result['total']],
                    ['Saved', $result['saved']],
                    ['Skipped (duplicates)', $result['skipped']],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to fetch articles: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
