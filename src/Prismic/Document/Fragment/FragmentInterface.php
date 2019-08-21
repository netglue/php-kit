<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Serializer\Serializer;

interface FragmentInterface
{

    public function asText() :? string;

    public function asHtml(?callable $serializer = null) :? string;

    public function serialize(Serializer $serializer);
}
