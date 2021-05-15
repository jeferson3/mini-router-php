<?php


namespace SimpleRouter\config;


final class Request
{

    /**
     * @param string $string
     * @return mixed|null
     */
    public function get(string $string)
    {
        return array_key_exists($string, $_REQUEST) ? $_REQUEST[$string] : null;
    }

    /**
     * @return object
     */
    public function all()
    {
        return (object) $_REQUEST;
    }

    /**
     * @param array $values
     * @return object
     */
    public function only(array $values)
    {
        $aux = array();
        foreach ($values as $value)
        {
            $aux[$value] = $this->get($value);
        }
        return (object) $aux;
    }

    /**
     * @param array $values
     * @return object
     */
    public function except(array $values)
    {
        foreach ($_REQUEST as $key => $value)
        {
            foreach ($values as $key2) {
                if (trim($key) == trim($key2)){
                    unset($_REQUEST[$key]);
                }
            }
        }
        return (object) $_REQUEST;
    }

}
