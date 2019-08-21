<?php
declare(strict_types=1);

namespace Prismic\Serializer;

use Prismic\Document\Fragment\FragmentInterface;

interface Serializer
{

    public function __invoke(FragmentInterface $fragment);

    public function serialize(FragmentInterface $fragment);
}
