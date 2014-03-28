<?php
namespace DataImportEngine\Writer;

use Ddeboer\DataImport\Writer\WriterInterface;
use Symfony\Component\Validator\ValidatorInterface;
use DataImportEngine\Writer\ObjectWriter\ObjectFactoryInterface;
use DataImportEngine\Writer\ObjectWriter\DefaultObjectFactory;

class ValidationObjectWriter implements WriterInterface
{

    private $line = 1;
    private $violations = array();
    private $skipOnViolation = true;

    /**
     * @var callable
     */
    private $objectHandler;

    /**
     * @var ObjectFactoryInterface
     */
    private $objectFactory;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct($classOrObjectFactory)
    {
        if (is_object($classOrObjectFactory) && $classOrObjectFactory instanceof ObjectFactoryInterface) {
            $objectFactory = $classOrObjectFactory;
        } elseif (is_string($classOrObjectFactory)) {
            $objectFactory = new DefaultObjectFactory($classOrObjectFactory);
        }

        $this->setObjectFactory($objectFactory);
    }

    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function setObjectFactory(ObjectFactoryInterface $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    public function setObjectHandler(callable $objectHandler)
    {
        $this->objectHandler = $objectHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function prepare()
    {
        return $this;
    }

    private function validate($object)
    {
        if (!$this->validator) {
            return;
        }

        $list = $this->validator->validate($object);

        if (count($list) > 0) {
            $this->violations[$this->line] = $list;
        }

        $this->line++;

        return !$this->skipOnViolation || 0 === count($list);
    }

    private function convert(array $item)
    {
        if (!$this->objectFactory) {
            throw new \LogicException("No objectFactory has been set yet!");
        }

        $object = $this->objectFactory->factor($item);

        return $object;
    }

    private function write($object)
    {
        if (!$this->objectHandler) {
            return;
        }

        call_user_func_array($this->objectHandler, array($object));
    }

    /**
     * (non-PHPdoc)
     * @see \Ddeboer\DataImport\Writer\WriterInterface::writeItem()
     */
    public function writeItem(array $item)
    {
        //convert
        $object = $this->convert($item);

        //validate
        if ($this->validate($object)) {

            //write
            $this->write($object);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function finish()
    {
        return $this;
    }
}
