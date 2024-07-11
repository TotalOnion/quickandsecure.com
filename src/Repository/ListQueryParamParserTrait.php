<?php

namespace App\Repository;

use App\Entity\Mapping;
use App\Exception\ApiQueryStringException;
use Symfony\Component\HttpFoundation\Request;

trait ListQueryParamParserTrait
{
    const MAX_RETURNED_RESULTS = 1000;

    private array $validOrderOnFields     = [];
    private array $validFilterOnFields    = [];
    private string $defaultOrderOnField   = '';
    private string $defaultOrderDirection = Mapping\DefaultOrderOnField::ORDER_DIRECTION_WHEN_NONE_IS_SET;
    private ?int $limit                   = null;
    private ?int $offset                  = null;

    public function parseFilter( Request $request ): array
    {
        if ( ! $request->query->get('filter') ) {
            return [];
        }

        $filterObject = json_decode( $request->query->get('filter'), true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            throw new ApiQueryStringException(
                'Malformed "filter" parameter. Correct format is filter={"foo":"bar","baz":true}'
            );
        }

        if ( ! $this->validFilterOnFields ) {
            $this->findPropertiesFromEntityClass();
        }

        foreach ( $filterObject as $filterKey => $value ) {
            // Check if the requested filter field is one we are allowed to filter on
            $this->validateRequestedFilterKey( $filterKey );
            

            // Check that the value type is the same (test bool against bool, string against string etc)
            $this->validateFilterComparisonValue( $filterKey, $value );
        }

        return $filterObject;
    }

    /**
     * Check to see if a filterKey is in the list of allowed filter keys
     */
    private function validateRequestedFilterKey( string $filterKey ):void
    {
        if ( ! array_key_exists( $filterKey, $this->validFilterOnFields ) ) {
            throw new ApiQueryStringException(
                sprintf(
                    'Invalid filter key of "%s". Accepted keys are %s',
                    $filterKey,
                    implode( ', ', array_keys( $this->validFilterOnFields ) )
                )
            );
        }
    }

    private function validateFilterComparisonValue(
        string $filterKey,
        mixed $comparisonValue
    ):void {
        $filterDefinition = $this->validFilterOnFields[ $filterKey ];
        $comparisonValueType = gettype( $comparisonValue );

        if ( $filterDefinition['canAcceptAnArray'] ) {
            // we _can_ accept an arry, but they've passed in just one of the right type
            if ( $comparisonValueType === $filterDefinition['expectedFieldType'] ) {
                return;
            }

            if ( ! is_array( $comparisonValue ) ) {
                throw new ApiQueryStringException(
                    sprintf(
                        'filter "%s". Expects a value of type %s or %s[], but %s was received.',
                        $filterKey,
                        $filterDefinition['expectedFieldType'],
                        $filterDefinition['expectedFieldType'],
                        $comparisonValueType
                    )
                );
            }

            foreach( $comparisonValue as $value ) {
                if ( gettype( $value ) !== $filterDefinition['expectedFieldType'] ) {
                    throw new ApiQueryStringException(
                        sprintf(
                            'Bad value in filter "%s". While this filter does accept an array, one or more of the values passed was of the type %s when %s was expected.',
                            $filterKey,
                            gettype( $value ),
                            $filterDefinition['expectedFieldType']
                        )
                    );
                }
            }
        } elseif ( $comparisonValueType !== $filterDefinition['expectedFieldType'] ) {
            throw new ApiQueryStringException(
                sprintf(
                    'filter "%s". Expects a value of type %s, but %s was received.',
                    $filterKey,
                    $filterDefinition['expectedFieldType'],
                    $comparisonValueType
                )
            );
        }
    }

    public function parseLimit( Request $request ):int
    {
        if ( ! $this->limit ) {
            $this->parseLimitAndOffset( $request );
        }

        return $this->limit;
    }

    public function parseOffset( Request $request ):int
    {
        if ( ! $this->offset ) {
            $this->parseLimitAndOffset( $request );
        }

        return $this->offset;
    }

    private function parseLimitAndOffset( Request $request ): void
    {
        if ( $request->query->get('range') ) {
            list($this->offset, $this->limit) = json_decode( $request->query->get('range') );

            if ( json_last_error() !== JSON_ERROR_NONE ) {
                throw new ApiQueryStringException(
                    'Malformed "range" parameter. Correct format is range=[0, 24]'
                );
            }

            if ( $this->offset < 0 || $this->limit < 0 ) {
                throw new ApiQueryStringException( 'Offset and range cannot be negative.' );
            }

            if ( $this->limit > self::MAX_RETURNED_RESULTS ) {
                throw new ApiQueryStringException( sprintf( 'Range cannot exceed %d.', self::MAX_RETURNED_RESULTS ) );
            }
        }

        if ( ! $this->offset ) {
            $this->offset = 0;
        }

        if ( ! $this->limit ) {
            $this->limit = 20;
        }
    }
    
    public function parseOrderBy( Request $request ):array
    {
        $orderOn = null;
        $orderDirection = null;

        // get the desired order on & direction from the query
        if ( $request->query->get('sort') ) {
            list( $orderOn, $orderDirection ) = json_decode( $request->query->get('sort') );

            if ( json_last_error() !== JSON_ERROR_NONE ) {
                throw new ApiQueryStringException(
                    sprintf(
                        'Malformed "sort" parameter. Correct format is sort=["field_name","ASC"]',
                        $orderDirection,
                        implode( ', ', Mapping\DefaultOrderOnField::VALID_ORDER_DIRECTIONS )
                    )
                );
            }
        }

        // If we don't have a field to order on, load the default from the associated entity
        if ( ! $orderOn ) {
            $orderOn = $this->getDefaultOrderOnField();
        }

        // If we don't have an order direction, load the default from the associated entity
        if ( ! $orderDirection ) {
            $orderDirection = $this->getDefaultOrderOnDirection();
        }

        // If we don't have them still, it means no Mapping\DefaultOrderOn attribute has been set
        if ( ! $orderOn || ! $orderDirection ) {
            return [];
        }

        // Check to see if the orderDirection is valid
        if ( ! in_array( $orderDirection, Mapping\DefaultOrderOnField::VALID_ORDER_DIRECTIONS ) ) {
            throw new ApiQueryStringException(
                sprintf(
                    'Requested "sort direction" of "%s" is not accepted. Acceptable values are %s.',
                    $orderDirection,
                    implode( ', ', Mapping\DefaultOrderOnField::VALID_ORDER_DIRECTIONS )
                )
            );
        }

        // Check to see if the order on field is one that we can actually use for this entity
        if ( ! in_array( $orderOn, $this->getValidOrderByFields() ) ) {
            throw new ApiQueryStringException(
                sprintf(
                    'Requested "sort by" field of "%s" is not accepted. Acceptable values are %s.',
                    $orderOn,
                    implode( ', ', $this->getValidOrderByFields() )
                )
            );
        }

        return [ $orderOn => $orderDirection ];
    }

    public function getValidOrderByFields():array
    {
        if ( empty( $this->validOrderOnFields ) ) {
            $this->findPropertiesFromEntityClass();
        }

        return $this->validOrderOnFields;
    }

    public function getDefaultOrderOnField():string
    {
        if ( ! $this->defaultOrderOnField ) {
            $this->findDefaultOrderOnFieldFromEntityClass();
        }

        return $this->defaultOrderOnField;
    }

    public function getDefaultOrderOnDirection():string
    {
        if ( ! $this->defaultOrderDirection ) {
            $this->findDefaultOrderOnFieldFromEntityClass();
        }

        return $this->defaultOrderDirection;
    }

    /**
     * Use reflection to look *the first* class property marked as DefaultOrderOnField
     */
    private function findDefaultOrderOnFieldFromEntityClass():void
    {
        $reflectionClass = new \ReflectionClass( $this->getClassName() );
        foreach ($reflectionClass->getProperties() as $property) {
            foreach ( $property->getAttributes( Mapping\DefaultOrderOnField::class ) as $attribute ) {
                $this->defaultOrderOnField = $property->getName();
                $defaultOrderOnAttributeInstance = $attribute->newInstance();
                $this->defaultOrderDirection = $defaultOrderOnAttributeInstance->getDefaultOrderDirection();
                break 2;
            }
        }
    }

    /**
     * Use reflection to look for any class properties marked as CanBeOrderedOn, or CanBeFilteredOn
     */
    private function findPropertiesFromEntityClass():void
    {
        $this->validOrderOnFields = [];

        $reflectionClass = new \ReflectionClass( $this->getClassName() );
        foreach ($reflectionClass->getProperties() as $property) {
            if ( $property->getAttributes( Mapping\CanBeOrderedOn::class ) ) {
                $this->validOrderOnFields[] = $property->getName();
            }

            foreach ( $property->getAttributes( Mapping\CanBeFilteredOn::class ) as $attribute ) {
                $canBeFilteredOnAttribute = $attribute->newInstance();
                $propertyVariableType = $property->getType()->getName();
                // gettype() uses different names for some var types. Fix them here
                switch ( $propertyVariableType ) {
                    case 'bool':
                        $propertyVariableType = 'boolean';
                        break;

                    case 'int':
                        $propertyVariableType = 'integer';
                        break;       
                }

                $this->validFilterOnFields[ $property->getName() ] = [
                    'expectedFieldType' => $propertyVariableType,
                    'canAcceptAnArray'  => $canBeFilteredOnAttribute->canAcceptAnArray(),
                ];
            }
        }
    }
}
