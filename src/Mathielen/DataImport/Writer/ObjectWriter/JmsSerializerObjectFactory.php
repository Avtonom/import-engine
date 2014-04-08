<?php
namespace Mathielen\DataImport\Writer\ObjectWriter;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;

class JmsSerializerObjectFactory implements ObjectFactoryInterface
{

    private $classname;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct($classname, SerializerInterface $serializer)
    {
        $this->classname = $classname;
        $this->serializer = $serializer;
    }

    public function getClassname()
    {
        return $this->classname;
    }

    public function factor(array $item)
    {
        $json = json_encode($item);
        $object = $this->serializer->deserialize($json, $this->classname, 'json');

        return $object;
    }
}
