<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Composer;

use Symfony\Component\ClassLoader\ClassCollectionLoader;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * A hack to work around the missing support for js assets in composer
 *
 * @see http://groups.google.com/group/composer-dev/browse_thread/thread/e9e2f7d919aadfec
 *
 * @author David Buchmann
 */
class ScriptHandler
{
    public static function initSubmodules($event)
    {
        $status = null;
        $output = array();
        $dir = getcwd();
        chdir(__DIR__.DIRECTORY_SEPARATOR.'..');
        exec('git submodule sync', $output, $status);
        if ($status) {
            chdir($dir);
            die("Running git submodule sync failed with $status\n");
        }
        exec('git submodule update --init --recursive', $output, $status);
        chdir($dir);
        if ($status) {
            die("Running git submodule --init --recursive failed with $status\n");
        }
    }

    public static function downloadCkeditor($event)
    {
        $directory = __DIR__ . '/../Resources/public/vendor';
        $repository = 'https://github.com/ckeditor/ckeditor-releases.git';
        $commitId = 'bba29309f93a1ace1e2e3a3bd086025975abbad0';

        ScriptHandler::gitSynchronize($directory, $repository, 'ckeditor', $commitId);
    }

    /**
     * @param string $directory The parent directory where the repository should be clone into
     * @param string $repository The git repository
     * @param string $name The name of the clone
     * @param string $commitId The commit id
     */
    public static function gitSynchronize($directory, $repository, $name, $commitId)
    {
        $currentDirectory = getcwd();

        $status = null;
        $output = array();
        chdir($directory);

        if (is_dir($name)) {
            chdir($name);
            exec("git remote update", $output, $status);
            if ($status) {
                die("Running git pull $repository failed with $status\n");
            }
        } else {
            exec("git clone $repository $name", $output, $status);
            if ($status) {
                die("Running git clone $repository failed with $status\n");
            }
        }

        exec("git checkout $commitId", $output, $status);
        if ($status) {
            die("Running git clone $repository failed with $status\n");
        }

        chdir($currentDirectory);
    }
}
