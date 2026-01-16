<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Application\Article\UseCase\ResyncArticlesUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:articles:resync',
    description: 'Resync articles from GNews API and update if content changed',
)]
class ResyncArticlesCommand extends Command
{
    private ResyncArticlesUseCase $resyncArticlesUseCase;

    public function __construct(ResyncArticlesUseCase $resyncArticlesUseCase)
    {
        parent::__construct();
        $this->resyncArticlesUseCase = $resyncArticlesUseCase;
    }

    protected function configure(): void
    {
        $this
            ->addOption('category', 'c', InputOption::VALUE_OPTIONAL, 'Article category (general, world, business, technology, etc.)')
            ->addOption('lang', 'l', InputOption::VALUE_OPTIONAL, 'Language code (en, ar, etc.)')
            ->addOption('max', 'm', InputOption::VALUE_OPTIONAL, 'Maximum number of articles to fetch per page', 10)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $category = $input->getOption('category');
        $lang = $input->getOption('lang');
        $max = (int) $input->getOption('max');

        $io->title('Resyncing Articles from GNews API');
        $io->info(sprintf('Category: %s, Language: %s, Max per page: %d', $category ?? 'general', $lang ?? 'en', $max));
        $io->note('This will check existing articles and update them if content has changed.');

        try {
            $result = $this->resyncArticlesUseCase->execute($category, $lang, $max);

            $io->success('Articles resynced successfully!');
            $io->table(
                ['Metric', 'Count'],
                [
                    ['Pages fetched', $result['pages']],
                    ['Total fetched', $result['total']],
                    ['New articles', $result['new']],
                    ['Updated articles', $result['updated']],
                    ['Unchanged articles', $result['unchanged']],
                ]
            );

            if ($result['updated'] > 0) {
                $io->note(sprintf('%d article(s) were updated with new content.', $result['updated']));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to resync articles: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
