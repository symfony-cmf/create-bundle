<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Workflow;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Midgard\CreatePHP\WorkflowInterface;

/**
 * Class DirectDeleteWorkflow
 *
 * Implement direct deletion of an object.
 *
 * @author Uwe Jäger <uwej711@googlemail.com>
 */
class DoctrinePhpcrDeleteWorkflow implements WorkflowInterface
{
    /**
     * @var ObjectManager
     */
    protected $om;

    public function __construct(ManagerRegistry $registry, $name = null)
    {
        $this->om = $registry->getManager($name);
    }

    /**
     * Get toolbar config for the given object, if the workflow is applicable
     * and allowed.
     *
     * @see http://createjs.org/guide/#workflows
     *
     * @param mixed $object
     *
     * @return array|null Array to return for this workflow, or null if
     *                    workflow is not allowed.
     */
    public function getToolbarConfig($object)
    {
        return array(
            'name' => 'delete',
            'label' => 'delete',
            'action' => array(
                'type' => "confirm_destroy"
            ),
            'type' => "button"
        );
    }

    /**
     * Execute this workflow
     *
     * The object will only be set if there is a subject parameter in $_GET
     * that can be found by the mapper tied to the RestService.
     *
     * @param mixed $object
     *
     * @return array
     */
    public function run($object)
    {
        $this->om->remove($object);
        $this->om->flush();
    }

}
