<?php

namespace App\Repository;

use App\Entity\Mapping;
use Symfony\Component\HttpFoundation\Request;

trait ListQueryParamParserTrait
{
    private array $validOrderOnFields = [];

    public function parseOrderBy( Request $request )
    {
        print_r( $this->getValidOrderByFields() );

        /*
        $attrs = $t->getProperties();
        foreach ($attrs as $attribute) {
            echo $attribute->getName(); // "My\Attributes\ExampleAttribute"
            //echo $attribute->getArguments(); // ["Hello world", 42]
            //echo $attribute->newInstance();
        }*/
    }

    public function getValidOrderByFields():array
    {
        if ( empty( $this->validOrderOnFields ) ) {
            $this->findOrderablePropertiesFromEntityClass();
        }

        return $this->validOrderOnFields;
    }

    private function findOrderablePropertiesFromEntityClass():void
    {
        $this->validOrderOnFields = [];

        // Use reflection to look for any class properties marked as CanBeOrderedOn
        $reflectionClass = new \ReflectionClass( $this->getClassName() );
        foreach ($reflectionClass->getProperties() as $property) {
            if ( $property->getAttributes( Mapping\CanBeOrderedOn::class ) ) {
                $this->validOrderOnFields[] = $property->getName();
            }
        }
    }
}
