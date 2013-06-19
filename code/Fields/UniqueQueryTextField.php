<?php

/**
 * Class UniqueQueryTextField
 */
class UniqueQueryTextField extends TextField
{
    /**
     * @var
     */
    protected $callable;
    /**
     * @param callable $callable
     * @param null     $name
     * @param null     $title
     * @param string   $value
     * @param null     $maxLength
     * @param null     $form
     */
    public function __construct(callable $callable, $name, $title = null, $value = "", $maxLength = null, $form = null)
    {
        $this->callable = $callable;
        $this->addExtraClass('text');
        parent::__construct($name, $title, $value, $maxLength, $form);
    }
    /**
     * @param $validator
     * @throws RuntimeException
     * @return bool
     */
    public function validate($validator)
    {
        $query = call_user_func($this->callable, $this->Value());
        if ($query instanceof SQLQuery) {
            $result = $query->execute()->value();
        } else {
            throw new RuntimeException('Function must return an instance of SQLQuery');
        }
        if ($result && ($result > 0)) {
            $validator->validationError($this->name, "The value entered is not unique");

            return false;
        }

        return true;
    }
}
