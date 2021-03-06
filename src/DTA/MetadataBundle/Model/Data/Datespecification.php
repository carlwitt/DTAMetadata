<?php

namespace DTA\MetadataBundle\Model\Data;

use DTA\MetadataBundle\Model\Data\om\BaseDatespecification;

class Datespecification extends BaseDatespecification {

    public function __toString() {
        $result = "";
        $result .= $this->getYear();

        if ($this->getYearIsReconstructed())
            $result = "[" . $result . "]";

        return $result;
    }

}