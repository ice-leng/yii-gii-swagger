<?php

function isJson($string)
{
    if (is_array($string)) {
        return $string;
    }
    $data = json_decode($string, true);
    return (json_last_error() == JSON_ERROR_NONE) ? is_array($data) : '';
}

function format($params, $type = 0, $exampleItems = [])
{
    $data = [];
    foreach ($params as $filed => $value) {
        if (is_null($value)) {
            continue;
        }
        if (in_array($value, ['true', 'false']) || ($filed === 'example' && isJson($value))) {
            $string = $filed . ' = ' . $value;
        } else {
            $string = $filed . ' = "' . $value . '"';
        }
        if ($filed === 'ref') {
            $string = 'ref="#/definitions/' . $value . '"';
            if ($type === 1) {
                $string = '@SWG\Schema(' . $string . ')';
            }
            if ($type === 2) {
                $string = 'items={"$ref":"#/definitions/'.$value.'"}';
                $itemData = [];
                foreach ($exampleItems as $item) {
                    $itemData[$item['property']] = $item['example'];
                }
                if (!empty($itemData)) {
                    $data[2] = 'example={' . json_encode($itemData) . '}';
                }
            }
        }
        $data[] = $string;
    }
    return $data;
}

function getParameter($parameters, $r)
{
    $parameterAnnotations = [];
    foreach ($parameters as $parameter) {
        $type = $parameter['in'] === 'body' ? 1 : 0;
        $annotations = implode(",\n *        ", format($parameter, $type));
        $parameterAnnotations[] = <<<parameterAnnotation
 *    
 *    @SWG\Parameter(
 *        {$annotations}
 *    )
parameterAnnotation;
    }
    $suffix = (!empty($r) ? ',' : '') . "\n";
    return !empty($parameterAnnotations) ? implode(",\n", $parameterAnnotations) . $suffix : '';
}

function getResponses($responses)
{
    $responseAnnotations = [];
    foreach ($responses as $response) {
        $annotations = implode(",\n *        ", format($response, 1));
        $responseAnnotations[] = <<<responseAnnotation
 *    
 *    @SWG\Response(
 *        {$annotations}
 *    )
responseAnnotation;
    }
    return !empty($responseAnnotations) ? implode(",\n", $responseAnnotations) . "\n" : '';
}

function getDefinitions($definitions)
{
    $definitionAnnotations = [];
    foreach ($definitions as $name => $definition) {
        $definitionItems = [];
        foreach ($definition as $datum) {
            $type = $datum['type'] === 'array' ? 2 : 0;
            $item = [];
            if ($type) {
                $item = $definitions[$datum['ref']];
            }
            $definitionItem = implode(",\n *        ", format($datum, $type, $item));
            $definitionItems[] = <<<definitionItems
 *                
 *    @SWG\Property(
 *        {$definitionItem}
 *    )
definitionItems;
        }
        $definitionAnnotation = implode(",\n", $definitionItems);
        $definitionAnnotations[] = <<<definitionAnnotation
 *
 * @SWG\Definition(
 *    definition = "{$name}",
{$definitionAnnotation}
 * )
definitionAnnotation;
    }
    return !empty($definitionAnnotations) ? implode(",\n", $definitionAnnotations) . "\n" : '';
}

$r = getResponses($responses);
$p = getParameter($parameters, $r);
$d = [];
foreach ($definitions as $definition) {
    $d[$definition['definition']] = $definition['data'];
}

?>
 * <?= $start . "\n" ?>
 * @SWG\<?= $info['method'] ?>(
 *    path="<?= $info['path'] ?>",
 *    tags=<?= $info['tags'] ?>,
 *    summary="<?= $info['summary'] ?>",
 *    description="<?= $info['description'] ?>",
 *    consumes=<?= $info['consumes'] ?>,
 *    produces=<?= $info['produces'] ?>,
<?= $info['security'] === 'true' ? (' *    security={{"api_key":{}}},'."\n")  : '' ?>
<?= $info['deprecated'] === 'true' ? (" *    deprecated=true,\n")  : '' ?>
<?= $p ?>
<?= $r ?>
 * )
<?= getDefinitions($d) ?>
 * <?= $end ?>