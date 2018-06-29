<?php
$code = 'new Date().getTime()';

$v8 = new V8Js();
$v8->executeString("print('hello world');");

exit;

/**
 * {@inheritdoc}
 */
/**
 * Executes Javascript using V8Js
 *
 * @param string $js JS code to be executed
 * @return string    The execution response
 */
function executeJs($js)
{
    $this->initV8();
    ob_start();
    try {
        $this->v8->executeString($js);
    } catch (\V8JsScriptException $e) {
        ob_end_clean();
        throw $e;
    }
    return ob_get_clean();
}

?>