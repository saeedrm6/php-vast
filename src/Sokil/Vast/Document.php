<?php

namespace Sokil\Vast;

class Document
{    
    /**
     *
     * @var \DomDocument
     */
    private $xml;
    
    private $vastAdSequence = array();

    /**
     * Document constructor.
     *
     * @param \DOMDocument $xml
     */
    public function __construct(\DOMDocument $xml)
    {
        $this->xml = $xml;
    }

    public function toString()
    {
        return $this->xml->saveXML();
    }
    
    public function __toString()
    {
        return $this->toString();
    }
    
    public function toDomDocument()
    {
        return $this->xml;
    }
    
    /**
     * Create "Ad" section ov "VAST" node
     * @return \Sokil\Vast\Ad
     */
    private function createAdSection($type)
    {        
        // Check Ad type
        $adTypeClassName = '\\Sokil\\Vast\\Ad\\' . $type;
        if(!class_exists($adTypeClassName)) {
            throw new \Exception('Ad type ' . $type . ' not allowed');
        }
        
        // create dom node
        $adDomElement = $this->xml->createElement('Ad');
        $this->xml->documentElement->appendChild($adDomElement);

        // Create type element
        $adTypeDomElement = $this->xml->createElement($type);
        $adDomElement->appendChild($adTypeDomElement);
        
        // create ad section
        $adSection = new $adTypeClassName($adDomElement);
        
        // cache
        $this->vastAdSequence[] = $adSection;
        
        return $adSection;
    }
    
    /**
     * 
     * @return \Sokil\Vast\Ad\InLine
     */
    public function createInLineAdSection()
    {
        return $this->createAdSection('InLine');
    }
    
    /**
     * 
     * @return \Sokil\Vast\Ad\Wrapper
     */
    public function createWrapperAdSection()
    {
        return $this->createAdSection('Wrapper');
    }
    
    public function getAdSections()
    {
        if(!$this->vastAdSequence) {
            
            foreach($this->xml->documentElement->childNodes as $adDomElement) {
                
                // get Ad tag
                if(!($adDomElement instanceof \DOMElement)) {
                    continue;
                }
                
                if('ad' !== strtolower($adDomElement->tagName)) {
                    continue;
                }

                // get Ad type tag
                foreach($adDomElement->childNodes as $node) {
                    if(!($node instanceof \DomElement)) {
                        continue;
                    }
                    
                    $type = $node->tagName;

                    // create ad section
                    $adTypeClassName = '\\Sokil\\Vast\\Ad\\' . $type;
                    if(!class_exists($adTypeClassName)) {
                        throw new \Exception('Ad type ' . $type . ' not allowed');
                    }

                    $this->vastAdSequence[] = new $adTypeClassName($adDomElement);
                    break;
                }
            }
        }
        
        
        return $this->vastAdSequence;
    }
}