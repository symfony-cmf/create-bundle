<?php

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends TestKernel
{
    public function configure()
    {
        $this->requireBundleSets(array(
            'default',
            'phpcr_odm',
        ));

        $this->addBundles(array(
            new \Symfony\Cmf\Bundle\CreateBundle\CmfCreateBundle(),
            new \FOS\RestBundle\FOSRestBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle($this),
        ));
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.php');
    }

    /**
     * Returns the kernel parameters.
     *
     * @return array An array of kernel parameters
     */
    protected function getKernelParameters()
    {
        return array_merge(
            parent::getKernelParameters(),
            array('kernel.cmf_test_web_dir' => CMF_TEST_ROOT_DIR . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'web')
        );
    }
}
