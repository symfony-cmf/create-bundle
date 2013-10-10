<?php

namespace Symfony\Cmf\Bundle\CreateBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CmfCreateBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if ($container->hasExtension('jms_di_extra')) {
            $container->getExtension('jms_di_extra')->blackListControllerFile(__DIR__ . '/Controller/ImageController.php');
        }
    }
}
