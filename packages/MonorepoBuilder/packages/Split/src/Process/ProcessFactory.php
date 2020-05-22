<?php declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Split\Process;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use someNamespaceA\NamespacedClass;
use Symfony\Component\Process\Process;
use Symplify\MonorepoBuilder\Split\Configuration\RepositoryGuard;

final class ProcessFactory
{
    /**
     * @var string
     */
    private const SUBSPLIT_BASH_FILE = __DIR__ . '/../../bash/subsplit.sh';

    /**
     * @var string
     */
    private $repository;

    /**
     * @var string
     */
    private $subsplitCacheDirectory;

    /**
     * @var RepositoryGuard
     */
    private $repositoryGuard;

    /**
     * @var string
     */
    private $gitSplitTool;

    public function __construct(
        RepositoryGuard $repositoryGuard,
        string $subsplitCacheDirectory,
        string $repository,
        string $gitSplitTool
    ) {
        $this->repositoryGuard = $repositoryGuard;
        $this->subsplitCacheDirectory = $subsplitCacheDirectory;
        $this->repository = $repository;
        $this->gitSplitTool = $gitSplitTool;
    }

    public function createSubsplit(
        ?string $theMostRecentTag,
        string $directory,
        string $remoteRepository,
        string $branch = 'master'
    ): Process {
        $this->repositoryGuard->ensureIsRepository($remoteRepository);

        $commandLine = [
            realpath(self::SUBSPLIT_BASH_FILE),
            sprintf('--from-directory=%s', $directory),
            sprintf('--to-repository=%s', $remoteRepository),
            sprintf('--branch=%s', $branch),
            $theMostRecentTag ? sprintf('--tag=%s', $theMostRecentTag) : '',
            sprintf('--repository=%s', $this->repository),
            sprintf('--tool=%s', $this->gitSplitTool)
        ];

        return $this->createProcessFromCommandLine($commandLine, $directory);
    }

    /**
     * @param mixed[] $commandLine
     */
    private function createProcessFromCommandLine(array $commandLine, string $directory): Process
    {
        $directory = $this->subsplitCacheDirectory . DIRECTORY_SEPARATOR . Strings::webalize($directory);

        FileSystem::delete($directory);
        FileSystem::createDir($directory);

        return new Process($commandLine, $directory, null, null, null);
    }
}
