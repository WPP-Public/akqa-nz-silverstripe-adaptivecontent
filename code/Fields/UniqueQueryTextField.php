<?php

/**
 * Class UniqueQueryTextField
 */
class UniqueQueryTextField extends TextField
{
    /**
     * @var
     */
    protected $closure;
    /**
     * @param Closure $closure
     * @param null     $name
     * @param null     $title
     * @param string   $value
     * @param null     $maxLength
     * @param null     $form
     */
    function __construct(Closure $closure, $name, $title = null, $value = "", $maxLength = null, $form = null)
    {
        $this->closure = $closure;
        parent::__construct($name, $title, $value, $maxLength, $form);
    }
    /**
     * @param $validator
     * @throws RuntimeException
     * @return bool
     */
    function validate($validator)
    {
        $query = $this->closure->__invoke($this->Value());
        if ($query instanceof SQLQuery) {
            $result = $query->execute()->value();
        } else {
            throw new RuntimeException('Closure must request an instance of SQLQuery');
        }
        if ($result && ($result > 0)) {
            $validator->validationError($this->name, "The value entered is not unique");

            return false;
        }

        return true;
    }
}