<?php

namespace FlowUI\Processors;

use DOMDocument;

interface ProcessorInterface
{
    /**
     * Process the DOM document
     * 
     * @param DOMDocument $dom The DOM document to process
     * @return void
     */
    public function process(DOMDocument $dom): void;
}
