<?php

namespace Drupal\node_csv_uploader;

use Drupal\node\Entity\NodeType;

class NodeTypes extends NodeType {

    protected $node;

    function __construct() {

    }

    function loadNode($node) {
        $this->node = $node;
    }

    function getName() {
        return $this->node->name;
    }
}