<?php

declare(strict_types=1);

namespace App\Presentation\Console;

use App\Application\Article\UseCase\GetArticleListUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:articles:list',
    description: 'List articles from database',
)]
class ListArticlesCommand extends Command
{
    private GetArticleListUseCase $getArticleListUseCase;

    public function __construct(GetArticleListUseCase $getArticleListUseCase)
    {
        parent::__construct();
        $this->getArticleListUseCase = $getArticleListUseCase;
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Number of articles to display', 10)
            ->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, 'Offset for pagination', 0)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $limit = (int) $input->getOption('limit');
        $offset = (int) $input->getOption('offset');

        $articleList = $this->getArticleListUseCase->execute($limit, $offset);
        $totalCount = $this->getArticleListUseCase->getTotalCount();

        $io->title('Articles List');
        $io->info(sprintf('Showing %d-%d of %d articles', $offset + 1, $offset + count($articleList), $totalCount));

        if (empty($articleList)) {
            $io->warning('No articles found in database. Run "php bin/console app:articles:fetch" to fetch articles.');
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($articleList as $article) {
            $rows[] = [
                substr($article->id, 0, 8) . '...',
                substr($article->title, 0, 50) . (strlen($article->title) > 50 ? '...' : ''),
                $article->sourceName,
                $article->publishedAt,
            ];
        }

        $io->table(
            ['ID', 'Title', 'Source', 'Published At'],
            $rows
        );

        return Command::SUCCESS;
    }
}
