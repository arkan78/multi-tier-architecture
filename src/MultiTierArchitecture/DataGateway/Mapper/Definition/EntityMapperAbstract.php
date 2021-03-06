<?php
namespace MultiTierArchitecture\DataGateway\Mapper\Definition;

use MultiTierArchitecture\Entity\Definition\EntityInterface;

/**
 * Mapper abstract class used to set arrays, array object of data that later need to be mapped to entities
 *
 * @category Mapper
 * @package  MultiTierArchitecture\DataGateway\Mapper\Definition
 * @author   Arkan M. Gerges <arkan.m.gerges@gmail.com>
 * @version  GIT: $Id:$
 */
abstract class EntityMapperAbstract
{
    /**
     * @var array  $mappingFirstEntityToSecondEntityAttributes  array with keys mapped to other entity's keys
     */
    private $mappingFirstEntityToSecondEntityAttributes = [];

    /**
     * @var array  $mappingSecondEntityToFirstEntityAttributes  array with keys mapped to entity's keys
     */
    private $mappingSecondEntityToFirstEntityAttributes = [];

    /**
     * @var array  $data  Data set and later need to be mapped to entities
     */
    protected $data = [];

    /**
     * The constructor will create construct the other mapping entity array
     *
     * @param array  $mappingFirstEntityToSecondEntityAttributes  Mapping array from base entities to others
     * @param array  $mappingSecondEntityToFirstEntityAttributes  Mapping array from other entities to base
     */
    public function __construct(
        $mappingFirstEntityToSecondEntityAttributes = [],
        $mappingSecondEntityToFirstEntityAttributes = []
    ) {
        $this->setMappingSecondEntityToFirstEntityAttributes($mappingSecondEntityToFirstEntityAttributes);
        $this->setMappingFirstEntityToSecondEntityAttributes($mappingFirstEntityToSecondEntityAttributes);

        $this->mappingFirstEntityToSecondEntityAttributes +=
            array_flip($this->mappingSecondEntityToFirstEntityAttributes);
        $this->mappingSecondEntityToFirstEntityAttributes +=
            array_flip($this->mappingFirstEntityToSecondEntityAttributes);
    }

    /**
     * Set mapping data
     *
     * @param array  $data  Mapping data
     *
     * @return void
     */
    public function setMappingFirstEntityToSecondEntityAttributes($data)
    {
        $this->mappingFirstEntityToSecondEntityAttributes = $data;
    }

    /**
     * Set mapping data
     *
     * @param array  $data  Mapping data
     *
     * @return void
     */
    public function setMappingSecondEntityToFirstEntityAttributes($data)
    {
        $this->mappingSecondEntityToFirstEntityAttributes = $data;
    }

    /**
     * Get mapping data
     *
     * @return array
     */
    public function getMappingFirstEntityToSecondEntityAttributes()
    {
        return $this->mappingFirstEntityToSecondEntityAttributes;
    }

    /**
     * Get mapping data
     *
     * @return array
     */
    public function getMappingSecondEntityToFirstEntityAttributes()
    {
        return $this->mappingSecondEntityToFirstEntityAttributes;
    }

    /**
     * Set array of arrays (e.g. [['name' => 'A', 'status' => 'B'], ['name' => 'C', 'status' => 'D']])
     *
     * @param array  $arrays  Array of arrays (represent data entity each of the array element)
     *
     * @return void
     */
    public function setArrays(array $arrays)
    {
        $this->data = (isset($arrays[0]) && is_array($arrays[0])) ?
                          $arrays :
                          [$arrays];
    }

    /**
     * Get second entities for the passed argument that represents the name of the second entity
     *
     * @param string  $secondEntityName  Second entity name
     *
     * @return array Array of entities
     */
    public function getMappedSecondEntitiesByEntityType($secondEntityName)
    {
        $secondEntities = [];
        foreach ($this->data as $data) {
            $secondEntity = $this->createAndReturnNewSecondEntity($data, $secondEntityName);
            if ($secondEntity) {
                $secondEntities[] = $secondEntity;
            }
        }

        return $secondEntities;
    }

    /**
     * Return a new second entity
     *
     * @param array   $data              Data array that represents entity's attributes
     * @param string  $secondEntityName  Name of the entity
     *
     * @return EntityInterface|null
     */
    private function createAndReturnNewSecondEntity($data, $secondEntityName)
    {
        /** @var EntityInterface $entity */
        $entity = $this->getSecondEntityByName($secondEntityName);
        if (is_array($data)) {
            return $this->mapAttributesFromFirstEntityToSecondEntityAndReturnEntity($data, $entity);
        }

        return null;
    }

    /**
     * Map $data attributes from entity to second entity attributes
     *
     * @param array            $data          Data array that represents entity's attributes
     * @param EntityInterface  $secondEntity  Second entity
     *
     * @return EntityInterface|null
     */
    private function mapAttributesFromFirstEntityToSecondEntityAndReturnEntity($data, $secondEntity)
    {
        $canAppendToSecondEntity = false;
        foreach ($data as $attributeName => $attributeData) {
            if (
                isset($this->mappingFirstEntityToSecondEntityAttributes[$attributeName])
            ) {
                $this->setSecondEntityAttribute($secondEntity, $attributeName, $attributeData);
                $canAppendToSecondEntity = true;
            }
        }

        if ($canAppendToSecondEntity) {
            return $secondEntity;
        }

        return null;
    }

    /**
     * Set second entity attribute based on the attribute name and attribute data
     *
     * @param EntityInterface  $secondEntity   Second entity name
     * @param string           $attributeName  Attribute name of the entity
     * @param mixed            $attributeData  Attribute data of the entity
     *
     * @return void
     */
    private function setSecondEntityAttribute($secondEntity, $attributeName, $attributeData)
    {
        $entityAttribute = $this->mappingFirstEntityToSecondEntityAttributes[$attributeName];
        $setterMethod    = 'set' . ucfirst($entityAttribute);

        $secondEntity->$setterMethod($attributeData);
    }

    /**
     * Get entities for the passed argument that represents the name of the entity
     *
     * @param string  $entityName  Entity name
     *
     * @return array Array of entities
     */
    public function getMappedFirstEntitiesByEntityType($entityName)
    {
        $entities = [];
        foreach ($this->data as $data) {
            $entity = $this->createAndReturnNewFirstEntity($data, $entityName);
            if ($entity) {
                $entities[] = $entity;
            }
        }
        return $entities;
    }

    /**
     * Add a new entities to the $entities parameter
     *
     * @param array   $data        Data array that represents entity's attributes
     * @param string  $entityName  Name of the entity
     *
     * @return EntityInterface|null
     */
    private function createAndReturnNewFirstEntity($data, $entityName)
    {
        if (is_array($data)) {
            $entity = $this->getFirstEntityByName($entityName);
            return $this->mapAttributesFromSecondEntityToFirstEntityAndReturnEntity($data, $entity);
        }

        return null;
    }

    /**
     * Map $data attributes to entity attributes
     *
     * @param array                                                     $data    Data array that
     *                                                                           represents entity's attributes
     * @param \MultiTierArchitecture\Entity\Definition\EntityInterface  $entity  Entity name
     *
     * @return EntityInterface|null
     */
    private function mapAttributesFromSecondEntityToFirstEntityAndReturnEntity($data, $entity)
    {
        $canAppendToEntity = false;
        foreach ($data as $attributeName => $attributeData) {
            if (
                isset($this->mappingSecondEntityToFirstEntityAttributes[$attributeName])
            ) {
                $this->setFirstEntityAttribute($entity, $attributeName, $attributeData);
                $canAppendToEntity = true;
            }
        }

        if ($canAppendToEntity) {
            return $entity;
        }

        return null;
    }

    /**
     * Set entity attribute based on the attribute name and attribute data
     *
     * @param \MultiTierArchitecture\Entity\Definition\EntityInterface  $entity         Entity name
     * @param string                                                    $attributeName  Attribute name of the entity
     * @param mixed                                                     $attributeData  Attribute data of the entity
     *
     * @return void
     */
    private function setFirstEntityAttribute($entity, $attributeName, $attributeData)
    {
        $entityAttribute = $this->mappingSecondEntityToFirstEntityAttributes[$attributeName];
        $setterMethod    = 'set' . ucfirst($entityAttribute);
        $entity->$setterMethod($attributeData);
    }

    /**
     * Set array object as an array of entities
     *
     * @param array  $array  Array of entities
     *
     * @return void
     */
    public function setArray(array $array)
    {
        foreach ($array as $objectOrArray) {
            $this->setDataArrayForObjectOrArray($objectOrArray);
        }
    }

    /**
     * Create data array for the passed $objectOrArray parameter
     *
     * @param EntityInterface|array  $objectOrArray  Object or array representing an entity
     *
     * @return void
     */
    private function setDataArrayForObjectOrArray($objectOrArray)
    {
        if ($objectOrArray instanceof EntityInterface) {
            $this->getAttributesFromObjectAndSetDataArray($objectOrArray);
        }
        elseif (is_array($objectOrArray)) {
            $this->data[] = $objectOrArray;
        }
    }

    /**
     * Get the attributes from the array object and set them into the 'data' array of this object
     *
     * @param EntityInterface  $object  Object or array representing an entity
     *
     * @return void
     */
    private function getAttributesFromObjectAndSetDataArray($object)
    {

        $this->data[] = $object->getAttributes();
    }

    /**
     * Get mapped attributes from the 'data' member mapped to second attributes
     *
     * @return array Attributes after mapping them into second attributes
     */
    public function getMappedSecondAttributes()
    {
        $attributes = [];

        /* Only the first array in data will be examined, and map its attributes to db attributes,
           the attributes that are not present, will not be included
        */
        if (is_array($this->data[0])) {
            foreach ($this->data[0] as $attribute => $attributeValue) {
                if (array_key_exists($attribute, $this->mappingFirstEntityToSecondEntityAttributes)) {
                    $attributes[$this->mappingFirstEntityToSecondEntityAttributes[$attribute]] = $attributeValue;
                }
            }
        }

        return $attributes;
    }

    /**
     * Get mapped attribute from the 'data' member mapped to second attribute for the passed parameter
     *
     * @param string  $attributeValue  Value that the caller wants its second mapping attribute to be fetched
     *
     * @return string Mapped second attribute for the passed parameter
     */
    public function getMappedSecondAttributeByOneAttributeValue($attributeValue)
    {
        return array_key_exists($attributeValue, $this->mappingFirstEntityToSecondEntityAttributes) ?
            $this->mappingFirstEntityToSecondEntityAttributes[$attributeValue] :
            '';
    }

    /**
     * Create second entity by name
     *
     * @param string  $secondEntityName  Entity name that need to be created
     *
     * @return mixed New entity
     */
    abstract public function getSecondEntityByName($secondEntityName);

    /**
     * Create entity by name
     *
     * @param string  $entityName  Entity name that need to be created
     *
     * @return mixed New entity
     */
    abstract public function getFirstEntityByName($entityName);

    /**
     * Get mapped second entities array, the entities are the same entities from
     * \MultiTierArchitecture\Entity\Definition\EntityInterface namespace
     *
     * @return array Array of entities
     */
    abstract public function getMappedSecondEntities();

    /**
     * Get mapped first entities array
     *
     * @return array Array of entities
     */
    abstract public function getMappedFirstEntities();
}
