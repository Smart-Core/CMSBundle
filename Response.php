<?php
/**
 * Перегружены 2 метода.
 * 
 * В отличие от оригинального класса, отрисовка контента производится только в момент получения контента,
 * а не в момент его установки.
 * 
 * Таким образом появляется возможность получить "чистый" контент вернувшийся от контроллера.
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

    /**
     * Получить контент в виде строки.
     * 
     * @return string
     */
    public function getContent()
    {
        return (string) $this->content;
    }

    /**
     * Получить контент в нативном виде т.е. если это будет объект, то он будет получен без преобразования в строку.
     * 
     * @return object|string
     */
    public function getContentRaw()
    {
        return $this->content;
    }
}