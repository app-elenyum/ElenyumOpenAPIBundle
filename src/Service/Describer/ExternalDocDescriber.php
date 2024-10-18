<?php

namespace Elenyum\OpenAPI\Service\Describer;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;

class ExternalDocDescriber implements DescriberInterface
{
    private $externalDoc;

    private $overwrite;

    /**
     * @param $options
     * @param bool $overwrite
     */
    public function __construct($options, bool $overwrite = false)
    {
        $this->externalDoc = $options['documentation'];
        $this->overwrite = $overwrite;
    }

    public function describe(OA\OpenApi $api)
    {
        $externalDoc = $this->getExternalDoc();

        if (!empty($externalDoc)) {
            Util::merge($api, $externalDoc, $this->overwrite);
        }
    }

    private function getExternalDoc()
    {
        if (is_callable($this->externalDoc)) {
            return call_user_func($this->externalDoc);
        }

        return $this->externalDoc;
    }
}
