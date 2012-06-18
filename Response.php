<?php
/**
 * Перегружены 2 метода.
 * Теперь отрисовка контента производится только в момент получения контента, а не в момент его установки.
 */
namespace SmartCore\Bundle\EngineBundle;

use Symfony\Component\HttpFoundation\Response as BaseResponse;

class Response extends BaseResponse
{
    public function setContent($content)
    {
        if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable(array($content, '__toString'))) {
            throw new \UnexpectedValueException('The Response content must be a string or object implementing __toString(), "'.gettype($content).'" given.');
        }

        $this->content = $content;

        return $this;
    }


    public function getContent()
    {
        return (string) $this->content;
    }
}