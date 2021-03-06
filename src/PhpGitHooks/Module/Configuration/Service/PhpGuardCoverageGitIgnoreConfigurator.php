<?php

namespace PhpGitHooks\Module\Configuration\Service;

use Bruli\EventBusBundle\CommandBus\CommandBus;
use Bruli\EventBusBundle\QueryBus\QueryBus;
use PhpGitHooks\Module\Git\Contract\Command\GitIgnoreWriter;
use PhpGitHooks\Module\Git\Contract\Query\GitIgnoreExtractor;
use PhpGitHooks\Module\Git\Contract\Response\GitIgnoreDataResponse;

class PhpGuardCoverageGitIgnoreConfigurator
{
    const FILE_TO_IGNORE = '.guard_coverage';
    /**
     * @var QueryBus
     */
    private $queryBus;
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * PhpGuardCoverageGitIgnoreConfigurator constructor.
     *
     * @param QueryBus   $queryBus
     * @param CommandBus $commandBus
     */
    public function __construct(QueryBus $queryBus, CommandBus $commandBus)
    {
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function configure()
    {
        /** @var GitIgnoreDataResponse $gitIgnoreContent */
        $gitIgnoreContent = $this->queryBus->handle(new GitIgnoreExtractor());

        if (false === $this->isFileIgnored($gitIgnoreContent->getContent())) {
            $content = $this->getContent($gitIgnoreContent->getContent());

            $this->commandBus->handle(new GitIgnoreWriter($content));
        }
    }

    /**
     * @param $data
     *
     * @return bool
     */
    private function isFileIgnored($data)
    {
        return false !== strpos($data, static::FILE_TO_IGNORE);
    }

    /**
     * @param string $gitIgnoreContent
     *
     * @return string
     */
    private function getContent($gitIgnoreContent)
    {
        return trim($gitIgnoreContent)."\n#Entry generated by php-git-hooks tool.\n".self::FILE_TO_IGNORE;
    }
}
