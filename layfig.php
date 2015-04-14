<?php
// 判断Layload是否已经定义
if(defined('INIT_LAYFIG')) {
    return;
}
// 定义标记
define('INIT_LAYFIG', true);

/**
 * 节点数据接口类
 * 
 * @author Lay Li
 *        
 */
interface ILayfig {
    /**
     * 设置节点的值
     *
     * @param mixed $key            
     * @param mixed $value            
     * @return void
     */
    public function set($key, $value);
    /**
     * 获取节点的值
     *
     * @param mixed $key            
     * @return mixed
     */
    public function get($key);
}

/**
 * 默认节点数据实现类
 *
 * @author Lay Li
 *        
 */
class Layfig implements ILayfig {
    private static $instances = array();
    /**
     *
     * @param string $appname
     *            app名称
     * @return ILayfig
     */
    public static function getInstance($appname = 'layfig', $classname = 'Layfig') {
        if(! self::checkAppname($appname)) {
            return null;
        }
        if(! isset(self::$instances[$appname]) || ! self::$instances[$appname]) {
            self::$instances[$appname] = self::getInstanceByClassname($classname);
        }
        return self::$instances[$appname];
    }
    /**
     * 通过类名获取一个实例
     *
     * @param string $classname
     *            类名
     * @return ILayfig
     */
    public static function getInstanceByClassname($classname = 'Layfig') {
        $class = null;
        if(self::checkClassname($classname)) {
            $class = new $classname();
        }
        if(! ($class instanceof ILayfig)) {
            unset($class);
        }
        return $class;
    }
    /**
     * 获取某个app的节点值
     *
     * @param mixed $keystr
     *            要获取的节点键名
     * @param string $appname
     *            app名称
     * @param string $classname
     *            类名
     * @return mixed
     */
    public static function getter($keystr, $appname = 'layfig', $classname = 'Layfig') {
        return self::getInstance($appname, $classname)->get($keystr);
    }
    /**
     * 设置某个app的节点值
     *
     * @param mixed $keystr
     *            要设置的节点键名
     * @param mixed $value
     *            要设置的节点值
     * @param string $appname
     *            app名称
     * @param string $classname
     *            类名
     * @return void
     */
    public static function setter($keystr, $value, $appname = 'layfig', $classname = 'Layfig') {
        self::getInstance($appname, $classname)->set($keystr, $value);
    }
    /**
     * 检测是否符合规定的格式，只支持string
     *
     * @param string $appname
     *            app名称
     * @return boolean
     */
    private static function checkAppname($appname) {
        if(is_string($appname)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 检测是否符合类名格式
     *
     * @param string $classname
     *            类名
     * @return boolean
     */
    private static function checkClassname($classname) {
        if(is_string($classname) && $classname && class_exists($classname)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 数据根节点
     *
     * @var array
     */
    protected $configuration = array();
    protected function __construct() {
    }
    /**
     * 获取节点的值
     *
     * @param array|string|int $keystr
     *            要获取的节点键名
     * @return array string number boolean
     */
    public function get($keystr, $default = null) {
        if($this->checkKey($keystr)) {
            if(is_array($keystr) && $keystr) {
                $node = array();
                foreach($keystr as $i => $key) {
                    $node[$i] = $this->get($key);
                }
            } else if(is_string($keystr) && $keystr) {
                $node = &$this->configuration;
                $keys = explode('.', $keystr);
                foreach($keys as $key) {
                    if(isset($node[$key])) {
                        $node = &$node[$key];
                    } else {
                        return $default;
                    }
                }
            } else {
                $node = &$this->configuration;
            }
            return $node;
        } else {
            Debugger::debug('given key isnot supported;string,int is ok.', 'LAYFIG');
            return null;
        }
    }
    /**
     * 设置节点的值
     *
     * @param array|string|int $keystr
     *            要设置的节点键名
     * @param array|string|number|boolean $value
     *            要设置的节点值
     * @return void
     */
    public function set($keystr, $value) {
        if(! $this->checkKey($keystr)) {
            Debugger::warn('given key isnot supported;string,int is ok.', 'LAYFIG');
        } else {
            if(! $this->checkValue($value)) {
                Debugger::warn('given value isnot supported;string,number,boolean is ok.', 'LAYFIG');
            } else {
                if(! $this->checkKeyValue($keystr, $value)) {
                    Debugger::warn('given key and value isnot match;if key is array,value must be array.', 'LAYFIG');
                } else {
                    $node = &$this->configuration;
                    if(is_array($keystr) && $keystr) {
                        foreach($keystr as $i => $key) {
                            $this->set($key, isset($value[$i]) ? $value[$i] : false);
                        }
                    } else if(is_string($keystr) && $keystr) {
                        $keys = explode('.', $keystr);
                        $count = count($keys);
                        foreach($keys as $index => $key) {
                            if(isset($node[$key]) && $index === $count - 1) {
                                // TODO warning has been configured by this name
                                Debugger::warn('$configuration["' . implode('"]["', $keys) . '"] has been configured.', 'LAYFIG');
                                $node[$key] = $value;
                            } else if(isset($node[$key])) {
                                $node = &$node[$key];
                            } else if($index === $count - 1) {
                                $node[$key] = $value;
                            } else {
                                $node[$key] = array();
                                $node = &$node[$key];
                            }
                        }
                    }
                }
            }
        }
    }
    /**
     * 检测是否符合规定的格式，支持array,string,int,且数组中也必须符合此格式
     *
     * @param array|string|int $key
     *            节点键名
     * @return boolean
     */
    private function checkKey($key) {
        if(is_array($key)) {
            foreach($key as $i => $k) {
                if(! $this->checkKey($k)) {
                    return false;
                }
            }
            return true;
        } else if(is_string($key) || is_int($key)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 检测是否符合规定的格式，支持array,string,number,boolean,且数组中也必须符合此格式
     *
     * @param array|string|number|boolean $value
     *            节点值
     * @return boolean
     */
    private function checkValue($value) {
        if(is_array($value)) {
            foreach($value as $i => $var) {
                if(! $this->checkValue($var)) {
                    return false;
                }
            }
            return true;
        } else if(is_bool($value) || is_string($value) || is_numeric($value)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     *
     * @param array $key            
     * @param array $values            
     */
    private function checkKeyValue($key, $value) {
        if(is_array($key)) {
            if(is_array($value)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
?>