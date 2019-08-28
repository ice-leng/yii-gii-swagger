<?php

namespace lengbin\gii\swagger;

use yii\gii\CodeFile;
use yii\helpers\Json;
use yii\web\Response;

class Generator extends \yii\gii\Generator
{

    public $viewPath;
    public $path;
    public $method;
    public $summary;
    public $description;
    public $produces;
    public $consumes;
    public $deprecated;
    public $security;

    public $tag;
    public $parameter;
    public $response;
    public $definition;
    public $tags;
    public $parameters;
    public $responses;
    public $definitions;

    public $parameterIn;
    public $parameterType;
    public $parameterName;
    public $parameterDescription;
    public $parameterRequired;
    public $parameterDefault;

    public $responseStatus;
    public $responseDescription;

    public $ref;

    public function init()
    {
        parent::init();

        if ($this->deprecated === null) {
            $this->deprecated = 'false';
        }
        if ($this->security === null) {
            $this->security = 'true';
        }
        if ($this->viewPath === null) {
            $this->viewPath = \Yii::$app->basePath . '/swagger';
        }
        if ($this->produces === null) {
            $this->produces[] = 'application/json';
            $this->produces[] = 'application/xml';
        }
        if ($this->consumes === null) {
            $this->consumes[] = 'application/x-www-form-urlencoded';
        }
    }

    /**
     * @return string name of the code generator
     */
    public function getName()
    {
        return 'Swagger Generator';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'This generator generates swagger annotation.';
    }

    protected function cacheSwagger()
    {
        $swaggerDirKey = 'swagger-dir';
        $dirs = \Yii::$app->cache->get($swaggerDirKey);
        $dirs = $dirs ? $dirs : [];
        if (empty($dirs[$this->path])) {
            $dirs[$this->path] = $this->tags[0];
        }
        $file = $dirs[$this->path];
        if ($file === 'swagger') {
            $file = 'swagger-tag';
        }
        return \Yii::getAlias($this->viewPath) . '/' . $file . '.php';
    }

    public function save($files, $answers, &$results)
    {
        $pathKey = 'swagger-path';
        $swaggerKey = 'swagger-' . $this->path;
        $swaggerDirKey = 'swagger-dir';
        $swaggerDefinitionKey = 'swagger-definition';
        \Yii::$app->cache->set($swaggerKey, $this);
        $paths = \Yii::$app->cache->get($pathKey);
        $paths = $paths ? $paths : [];
        if (!in_array($this->path, $paths)) {
            $paths[] = $this->path;
        }
        \Yii::$app->cache->set($pathKey, $paths);
        $dirs = \Yii::$app->cache->get($swaggerDirKey);
        $dirs = $dirs ? $dirs : [];
        if (empty($dirs[$this->path])) {
            $dirs[$this->path] = $this->tags[0];
        }
        \Yii::$app->cache->set($swaggerDirKey, $dirs);

        $swaggerDefinition = \Yii::$app->cache->get($swaggerDefinitionKey);
        $swaggerDefinition = $swaggerDefinition ? $swaggerDefinition : [];

        if (!empty($this->definitions)) {
            $responses = $this->formateResponses();
            $swaggerDefinition[$this->path] = $responses[200]['ref'];
        }

        \Yii::$app->cache->set($swaggerDefinitionKey, $swaggerDefinition);

        return parent::save($files, $answers, $results);
    }

    protected function generateInfo()
    {
        return [
            'path'        => $this->path,
            'method'      => $this->method,
            'tags'        => '{"' . implode('", "', $this->tags) . '"}',
            'summary'     => $this->summary,
            'description' => $this->description,
            'produces'    => '{"' . implode('", "', $this->produces) . '"}',
            'consumes'    => '{"' . implode('", "', $this->consumes) . '"}',
            'deprecated'  => $this->deprecated,
            'security'  => $this->security,
        ];
    }

    protected function generateParameters()
    {
        $parameters = [];
        if (!empty($this->parameters)) {
            $pFields = [
                'parameterName'        => 'name',
                'parameterDescription' => 'description',
                'parameterIn'          => 'in',
                'parameterType'        => 'type',
                'parameterRequired'    => 'required',
                'ref'                  => 'ref',
                'parameterDefault'     => 'default',
            ];
            foreach ($this->parameters['parameterName'] as $pk => $pv) {
                $pData = [];
                foreach ($pFields as $pField => $pChange) {
                    $pData[$pChange] = null;
                    if (!empty($this->parameters[$pField])) {
                        $pData[$pChange] = array_key_exists($pk, $this->parameters[$pField]) ? $this->parameters[$pField][$pk] : null;
                    }
                }
                if (empty($pData['name'])) {
                    continue;
                }
                $parameters[] = $pData;
            }
        }
        return $parameters;
    }

    protected function successResponse($definition, $ref)
    {
        return [
            'definition' => $definition,
            'data'       => [
                [
                    'property'    => 'code',
                    'description' => 'code',
                    'example'     => 0,
                    'type'        => 'integer',
                    'ref'         => null,
                ],
                [
                    'property'    => 'message',
                    'description' => '提示',
                    'example'     => 'success',
                    'type'        => 'string',
                    'ref'         => null,
                ],
                [
                    'property'    => 'data',
                    'description' => '返回数据',
                    'example'     => null,
                    'type'        => 'object',
                    'ref'         => $ref,
                ],
            ],
        ];
    }

    protected function formateResponses()
    {
        $responses = [];
        if (!empty($this->responses)) {
            $pFields = [
                'responseStatus'      => 'response',
                'responseDescription' => 'description',
                'ref'                 => 'ref',
            ];
            foreach ($this->responses['responseStatus'] as $pk => $pv) {
                $pData = [];
                foreach ($pFields as $pField => $pChange) {
                    $pData[$pChange] = null;
                    if (!empty($this->responses[$pField])) {
                        $pData[$pChange] = array_key_exists($pk, $this->responses[$pField]) ? $this->responses[$pField][$pk] : null;
                    }
                }
                $responses[$pv] = $pData;
            }
        }
        return $responses;
    }

    protected function generateResponses($definitions)
    {
        $responses = $this->formateResponses();
        if ($responses[200]['ref'] !== 'SuccessDefault') {
            $definitionSuccess = $responses[200]['ref'] . 'Success';
            $swaggerDefinitionKey = 'swagger-definition';
            $swaggerDefinition = \Yii::$app->cache->get($swaggerDefinitionKey);
            $swaggerDefinition = $swaggerDefinition ? $swaggerDefinition : [];
            if (!in_array($responses[200]['ref'], $swaggerDefinition) || isset($swaggerDefinition[$this->path])) {
                $successResponse = $this->successResponse($definitionSuccess, $responses[200]['ref']);
                array_unshift($definitions, $successResponse);
            }
            $responses[200]['ref'] = $definitionSuccess;
        }
        return [$responses, $definitions];
    }

    protected function generateDefinitions()
    {
        $data = [];
        if (!empty($this->definitions)) {
            foreach ($this->definitions as $definitions) {
                $pData = $dData = $cData = [];
                $definition = Json::decode($definitions);
                $cData['definition'] = $definition['definitionName'];
                foreach ($definition['data'] as $key => $datum) {
                    $start = strpos($key, '[');
                    $end = strpos($key, ']');
                    $name = substr($key, 0, $start);
                    $indexed = substr($key, $start + 1, ($end - $start - 1));
                    $pData[$name][$indexed] = $datum;
                }
                $pFields = [
                    'property'    => 'property',
                    'description' => 'description',
                    'example'     => 'example',
                    'type'        => 'type',
                    'ref'         => 'ref',
                ];
                foreach ($pData['property'] as $pk => $pv) {
                    foreach ($pFields as $pField => $pChange) {
                        $dData[$pChange] = null;
                        if (!empty($pData[$pField])) {
                            $dData[$pChange] = array_key_exists($pk, $pData[$pField]) ? $pData[$pField][$pk] : null;
                        }
                    }
                    $cData['data'][] = $dData;
                }
                $data[] = $cData;
            }
        }
        return $data;
    }

    /**
     * Generates the code based on the current user input and the specified code template files.
     * This is the main method that child classes should implement.
     * Please refer to [[\yii\gii\generators\controller\Generator::generate()]] as an example
     * on how to implement this method.
     * @return array
     */
    public function generate()
    {
        $info = $this->generateInfo();
        $parameters = $this->generateParameters();
        $definitions = $this->generateDefinitions();
        list($responses, $definitions) = $this->generateResponses($definitions);
        $make = str_replace('/', '.', $this->path);
        $params = [
            'start'       => "--- {$make} start ---",
            'end'         => "--- {$make} end ---",
            'info'        => $info,
            'parameters'  => $parameters,
            'responses'   => $responses,
            'definitions' => $definitions,
        ];
        $content = $this->render('annotation.php', $params);
        $files = [];
        $filePath = $this->cacheSwagger();
        if (is_file($filePath)) {
            $regex = sprintf('~%s([\s\S]*)%s~m', $params['start'], $params['end']);
            $source = file_get_contents($filePath);
            if (preg_match($regex, $source)) {
                $content = substr($content, 3);
                $content = preg_replace($regex, $content, $source);
            } else {
                $content = "/**\n" . $content . "\n */";
                $content = $source . "\n" . $content;
            }
        } else {
            $content = "<?php\n/**\n" . $content . "\n */";
        }
        $files[] = new CodeFile($filePath, $content);
        return $files;
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['method', 'path', 'summary', 'description', 'produces', 'consumes', 'viewPath'], 'required'],
            [['tags', 'parameters', 'responses', 'definitions'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            ['tag', 'validateTags', 'skipOnEmpty' => false],
            ['responses', 'validateResponses', 'skipOnEmpty' => false],
            [['deprecated', 'security'], 'string'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'method'               => '请求方式',
            'path'                 => '请求地址',
            'tag'                  => '标签',
            'summary'              => '摘要',
            'deprecated'           => '是否弃用',
            'security'             => '是否token授权',
            'description'          => '描述',
            'consumes'             => '请求内容类型',
            'produces'             => '请求返回内容类型',
            'parameter'            => '请求参数设置',
            'response'             => '请求响应',
            'definition'           => '自定义参数',
            'viewPath'             => '文件路径',
            'parameterIn'          => '请求参数类型',
            'parameterName'        => '请求参数名称',
            'parameterDescription' => '请求描述',
            'parameterType'        => '参数类型',
            'ref'                  => '自定义参数',
            'parameterRequired'    => '是否必填',
            'parameterDefault'     => '默认值',
            'responseStatus'       => '返回状态',
            'responseDescription'  => '返回描述',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'path'     => '请求地址，唯一key, 可以通过请求地址回显数据<code>/v1/classroom/default/update</code>，
                           或者<code>/v1/classroom/default/update/{id}</code>',
            'tag'      => '可以关联多个标签',
            'produces' => '可以定义多个返回类型',
            'consumes' => '如果<code>文件上传</code>请勾选<code>multipart/form-data</code>, 如果请求参数为<code>body</code>，可以定义请求体数据类型<code>application/json, application/xml</code>',
            'viewPath' => '文件存放路径,
                绝对路径：<code>/var/www/basic/swagger</code>, 或者<code>@app/swagger</code>.',
        ]);
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return [
            "Get"     => "Get",
            "Post"    => "Post",
            "Put"     => "Put",
            "Delete"  => "Delete",
            "Options" => 'Options',
            "Head"    => 'Head',
            "Patch"   => "Patch",
        ];
    }

    /**
     * @return array
     */
    public function getContentTypes()
    {
        return [
            "application/json"                  => "application/json",
            "application/xml"                   => "application/xml",
            "application/x-www-form-urlencoded" => "application/x-www-form-urlencoded",
            "multipart/form-data"               => "multipart/form-data",
            "text/html"                         => "text/html",
        ];
    }

    public function getParameterIns()
    {
        return [
            'formData' => 'formData',
            'path'     => 'path',
            'query'    => 'query',
            'header'   => 'header',
            'body'     => 'body',
        ];
    }

    public function getParameterTypes()
    {
        return [
            'string'  => 'string',
            'number'  => 'number',
            'integer' => 'integer',
            'boolean' => 'boolean',
            'file'    => 'file',
        ];
    }

    public function getDefinitionTypes()
    {
        return [
            'string'  => 'string',
            'integer' => 'integer',
            'number'  => 'number',
            'boolean' => 'boolean',
            'array'   => 'array',
            'object'  => 'object',
        ];
    }

    public function getPageTemplate()
    {
        return [
            [
                'property'    => 'list',
                'description' => '列表',
                'type'        => 'array',
                'ref'         => '',
                'example'     => '',
            ],
            [
                'property'    => 'currentPage',
                'description' => '当前分页',
                'type'        => 'integer',
                'ref'         => '',
                'example'     => 1,
            ],
            [
                'property'    => 'pageSize',
                'description' => '分页大小',
                'type'        => 'integer',
                'ref'         => '',
                'example'     => 10,
            ],
            [
                'property'    => 'totalPage',
                'description' => '总分页',
                'type'        => 'integer',
                'ref'         => '',
                'example'     => 2,
            ],
            [
                'property'    => 'totalCount',
                'description' => '总条数',
                'type'        => 'integer',
                'ref'         => '',
                'example'     => 11,
            ],
        ];
    }

    public function validateTags($attribute, $params)
    {
        if (empty($this->tags)) {
            $this->addError('tag', '标签不能为空.');
        }
    }

    public function validateResponses($attribute, $params)
    {
        if (!in_array('200', $this->responses['responseStatus'])) {
            $this->addError('response', '状态码必须有200.');
        }
    }

    public function autoCompleteData()
    {
        $pathKey = 'swagger-path';
        $paths = \Yii::$app->cache->get($pathKey);
        $paths = $paths ? $paths : [];
        return ['path' => $paths];
    }

    public function actionPath()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $path = \Yii::$app->request->get('path');
        $swaggerKey = 'swagger-' . $path;
        $data = \Yii::$app->cache->get($swaggerKey);
        $data = $data ? $data : [];
        return [
            'has'  => !empty($data) ? 1 : 0,
            'data' => $data,
        ];
    }

    public function actionDefinition()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $definition = \Yii::$app->request->post('definition');
        $swaggerDefinitionKey = 'swagger-definition';
        $swaggerDefinition = \Yii::$app->cache->get($swaggerDefinitionKey);
        $swaggerDefinition = $swaggerDefinition ? $swaggerDefinition : [];
        return ['has' => in_array($definition, $swaggerDefinition)];
    }
}