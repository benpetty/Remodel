<?php

namespace Remodel\Resource;


use Remodel\Transformer;

/**
 * A resource Item represents a single instance of a transformed object.
 * 
 * @package Remodel\Resource
 */
class Item extends Resource
{
    /**
     * @param mixed $data
     * @param Transformer $transformer
     */
    public function __construct($data, Transformer $transformer)
    {
        $this->data = $data;
        $this->transformer = $transformer;
    }

    /**
     * Transform the Item into an associative array
     * 
     * @return array
     */
    public function toData()
    {
        // Transform the object
        $data = $this->transformer->transform($this->data);

        // Get needed includes
        $includes = $this->mapIncludes();

        // Process includes
        if( !empty($includes) ){
            $data = array_merge($data, $this->processIncludes($includes));
        }

        return $data;
    }

    /**
     * Process all the includes defined for this transformer.
     * 
     * @param array $includes
     * @return array
     */
    protected function processIncludes($includes)
    {
        $data = [];

        // Process the includes
        foreach( $includes as $include => $nested ){

            if( method_exists($this->transformer, $include) ){

                $resource = $this->transformer->{$include}($this->data);

                if( $resource === null ){
                    continue;
                }

                if( $resource instanceof Resource ){

                    if( $resource->transformer ){
                        $resource->transformer->setIncludes($nested);
                    }

                    $data[$include] = $resource->toData();
                }
                else {
                    $data[$include] = $resource;
                }
            }
        }

        return $data;
    }

    /**
     * Map the includes into array indexed by top-level include referencing the nested
     * includes (if any).
     * 
     * @return array
     */
    private function mapIncludes()
    {
        $includes = array_unique(array_merge($this->transformer->getIncludes(), $this->transformer->getUserIncludes()));

        $mappedIncludes = [];

        // Re-work the includes, indexed by top-level referencing the nested includes
        foreach( $includes as $include ){

            // Does this include reference a nested-include
            if( ($pos = strpos($include, '.')) !== false ){
                $index = substr($include, 0, $pos);
                $nestedInclude = substr($include, $pos+1);
            } else {
                $index = $include;
                $nestedInclude = null;
            }

            // This index doesn't exist yet, create it and set it to an empty array
            if( array_key_exists($index, $mappedIncludes) === false ){
                $mappedIncludes[$index] = [];
            }

            // We have a nested include, add it to the array
            if( $nestedInclude &&
                in_array($nestedInclude, $mappedIncludes[$index]) === false ){
                $mappedIncludes[$index][] = $nestedInclude;
            }
        }

        return $mappedIncludes;
    }
}