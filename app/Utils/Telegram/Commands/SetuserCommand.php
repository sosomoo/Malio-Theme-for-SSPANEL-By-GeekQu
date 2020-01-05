<?php

namespace App\Utils\Telegram\Commands;

use App\Models\User;
use App\Services\Config;
use App\Utils\Tools;
use App\Utils\Telegram\Process;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

/**
 * Class SetuserCommand.
 */
class SetuserCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'setuser';

    /**
     * @var string Command Description
     */
    protected $description = '';

    /**
     * {@inheritdoc}
     */
    public function handle($arguments)
    {
        $Update = $this->getUpdate();
        $Message = $Update->getMessage();

        // 消息 ID
        $MessageID = $Message->getMessageId();

        // 消息会话 ID
        $ChatID = $Message->getChat()->getId();

        // 触发用户
        $SendUser = [
            'id'       => $Message->getFrom()->getId(),
            'name'     => $Message->getFrom()->getFirstName() . ' ' . $Message->getFrom()->getLastName(),
            'username' => $Message->getFrom()->getUsername(),
        ];

        if (!in_array($SendUser['id'], Config::get('telegram_admins'))) {
            $AdminUser = User::where('is_admin', 1)->where('telegram_id', $SendUser['id'])->first();
            if ($AdminUser == null) {
                // 非管理员回复消息
                if (Config::get('enable_not_admin_reply') === true && Config::get('not_admin_reply_msg') != '') {
                    return self::reply(Config::get('not_admin_reply_msg'), $MessageID);
                }
            }
        }

        // 发送 '输入中' 会话状态
        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $User = null;
        $FindUser = null;
        if ($Message->getReplyToMessage() != null) {
            // 回复源消息用户
            $FindUser = [
                'id'       => $Message->getReplyToMessage()->getFrom()->getId(),
                'name'     => $Message->getReplyToMessage()->getFrom()->getFirstName() . ' ' . $Message->getReplyToMessage()->getFrom()->getLastName(),
                'username' => $Message->getReplyToMessage()->getFrom()->getUsername(),
            ];
            $User = Process::getUser($FindUser['id']);
            if ($User == null) {
                return self::reply(Config::get('no_user_found'), $MessageID);
            }
        }

        // 命令格式：
        // - /setuser [用户识别码] 选项 操作值

        // ############## 命令解析 ##############
        $UserCode = '';
        if ($User == null) {
            $Options = self::StrExplode($arguments, ' ', 3);
            if (count($Options) < 3) {
                return self::reply('没有提供选项或操作值.', $MessageID);
            }
            // 用户识别码
            $UserCode = $Options[0];
            // 选项
            $Option = $Options[1];
            // 操作值
            $value = $Options[2];
        } else {
            $Options = self::StrExplode($arguments, ' ', 2);
            if (count($Options) < 2) {
                return self::reply('没有提供选项或操作值.', $MessageID);
            }
            // 选项
            $Option = $Options[0];
            // 操作值
            $value = $Options[1];
        }
        // ############## 命令解析 ##############

        // ############## 用户识别码处理 ##############
        if ($User == null) {
            // 默认搜寻字段
            $useMethod = 'email';
            if (strpos($UserCode, ':') !== false) {
                // 如果指定了字段
                $UserCodeExplode = explode(':', $UserCode);
                $Search = $UserCodeExplode[0];
                $UserCode = $UserCodeExplode[1];
                $SearchMethods = [
                    'id' => [
                        'ID'
                    ],
                    'email' => [
                        '邮箱'
                    ],
                    'port' => [
                        '端口'
                    ],
                ];
                $useTempMethod = self::getOptionMethod($SearchMethods, $Search);
                if ($useTempMethod != '') {
                    $useMethod = $useTempMethod;
                }
            }
            $User = Process::getUser($UserCode, $useMethod);
            if ($User == null) {
                return self::reply(Config::get('no_user_found'), $MessageID);
            }
        }
        // ############## 用户识别码处理 ##############

        // ############## 字段选项处理 ##############
        $OptionMethods = [
            'is_admin' => [
                '设置管理员',
                '管理员',
            ],
            'enable' => [
                '启用',
                '用户启用',
            ],
            'money' => [
                '钱',
                '金钱',
                '余额'
            ],
            'port' => [
                '端口'
            ],
            'transfer_enable' => [
                '流量',
                '数据',
                '数据流量'
            ],
            'passwd' => [
                '连接密码'
            ],
            'method' => [
                '加密',
                '加密方式',
                '加密方法'
            ],
            'protocol' => [
                '协议'
            ],
            'protocol_param' => [
                '协参',
                '协议参数',
            ],
            'obfs' => [
                '混淆'
            ],
            'obfs_param' => [
                '混参',
                '混淆参数',
            ],
            'invite_num' => [
                '邀请数量'
            ],
            'node_group' => [
                '用户组',
                '用户分组',
            ],
            'class' => [
                '等级'
            ],
            'class_expire' => [
                '等级过期时间'
            ],
            'expire_in' => [
                '账户过期时间',
                '账号过期时间'
            ],
            'node_speedlimit' => [
                '限速'
            ],
            'node_connector' => [
                '客户端',
                '连接数',
            ],
        ];
        $useOptionMethod = self::getOptionMethod($OptionMethods, $Option);
        if ($useOptionMethod == '') {
            return self::reply(Config::get('data_method_not_found'), $MessageID);
        }
        // ############## 字段选项处理 ##############

        // ############## 字段数据增改值处理 ##############
        $old = $User->$useOptionMethod;
        switch ($useOptionMethod) {
            // ##############
            case 'enable':
            case 'is_admin':
                break;
            // ##############
            case 'port':
                // 支持正整数或 0 随机选择
                if (!is_numeric($value) || strpos($value, '-') === 0) {
                    return self::reply('提供的端口非数值，如要随机重置请指定为 0.', $MessageID);
                }
                if ((int) $value === 0) {
                    $value = Tools::getAvPort();
                }
                $temp = $User->setPort($value);
                if ($temp['ok'] === false) {
                    $text = [
                        '目标用户：' . $User->email,
                        '欲修改项：' . $useOptionMethod,
                        '当前值为：' . $old,
                        '欲修改为：' . $value,
                        '错误详情：' . $temp['msg'],
                    ];
                    return $this->replyWithMessage(
                        [
                            'text'                  => implode(PHP_EOL, $text),
                            'reply_to_message_id'   => $MessageID,
                        ]
                    );
                }
                $new = $User->$useOptionMethod;
                $User->$useOptionMethod = $new;
                break;
            // ##############
            case 'transfer_enable':
                // 支持的写法，不支持单位 B
                //  2kb | mb | gb | tb | pb     // 指定为该值得流量
                // +2kb | mb | gb | tb | pb     // 增加流量
                // -2kb | mb | gb | tb | pb     // 减少流量
                // *2                           // 以当前流量做乘法
                // /2                           // 以当前流量做除法
                if (strpos($value, ' ') !== false) return self::reply('处理出错.', $MessageID);
                $new = self::TrafficMethod($User->$useOptionMethod, $value);
                if ($new === null) return self::reply('处理出错.', $MessageID);
                $User->$useOptionMethod = $new;
                $old = Tools::flowAutoShow($old);
                $new = Tools::flowAutoShow($new);
                break;
            // ##############
            case 'expire_in':
            case 'class_expire':
                break;
            // ##############
            case 'obfs':
            case 'method':
            case 'protocol':
                // 支持系统中存在的协议、混淆、加密，且受可行性限制
                $MethodClass = 'set' . ucfirst($useOptionMethod);
                $temp = $User->$MethodClass($value);
                if ($temp['ok'] === true) {
                    $text = [
                        '目标用户：' . $User->email,
                        '被修改项：' . $useOptionMethod,
                        '修改前值：' . $old,
                        '修改后值：' . $User->$useOptionMethod,
                        '修改备注：' . $temp['msg'],
                    ];
                    return $this->replyWithMessage(
                        [
                            'text'                  => implode(PHP_EOL, $text),
                            'reply_to_message_id'   => $MessageID,
                        ]
                    );
                } else {
                    $text = [
                        '目标用户：' . $User->email,
                        '欲修改项：' . $useOptionMethod,
                        '当前值为：' . $old,
                        '欲修改为：' . $value,
                        '错误详情：' . $temp['msg'],
                    ];
                    return $this->replyWithMessage(
                        [
                            'text'                  => implode(PHP_EOL, $text),
                            'reply_to_message_id'   => $MessageID,
                        ]
                    );
                }
                break;
            // ##############
            case 'passwd':
            case 'obfs_param':
            case 'protocol_param':
                // 参数值中不允许有空格
                if (strpos($value, ' ') !== false) return self::reply('处理出错.', $MessageID);
                $new = $value;
                $User->$useOptionMethod = $new;
                break;
            // ##############
            case 'money':
                // 参数值中不允许有空格，结果会含小数 2 位
                // +2       // 增加余额
                // -2       // 减少余额
                // *2       // 以当前余额做乘法
                // /2       // 以当前余额做除法
                $value = explode(' ', $value)[0];
                $new = self::ComputingMethod($User->$useOptionMethod, $value, true);
                if ($new === null) return self::reply('处理出错.', $MessageID);
                $User->$useOptionMethod = $new;
                break;
            // ##############
            case 'class':
            case 'invite_num':
            case 'node_group':
            case 'node_connector':
            case 'node_speedlimit':
                // 参数值中不允许有空格
                // +2       // 增加值
                // -2       // 减少值
                // *2       // 以当前值做乘法
                // /2       // 以当前值做除法
                $value = explode(' ', $value)[0];
                $new = self::ComputingMethod($User->$useOptionMethod, $value, false);
                if ($new === null) return self::reply('处理出错.', $MessageID);
                $User->$useOptionMethod = $new;
                break;
            // ##############
            default:
                return self::reply('尚不支持.', $MessageID);
                break;
        }
        if ($User->save()) {
            $text = [
                '目标用户：' . $User->email,
                '被修改项：' . $useOptionMethod,
                '修改前为：' . $old,
                '修改后为：' . $new,
            ];
            return $this->replyWithMessage(
                [
                    'text'                  => implode(PHP_EOL, $text),
                    'reply_to_message_id'   => $MessageID,
                ]
            );
        } else {
            return $this->replyWithMessage(
                [
                    'text'                  => '保存出错',
                    'reply_to_message_id'   => $MessageID,
                ]
            );
        }
        // ############## 字段数据增改值处理 ##############

        // if ($ChatID > 0) {
        //     // 私人
        //     self::Privacy($User, $SendUser, $ChatID, $Message, $MessageID);
        // } else {
        //     // 群组
        //     self::Group($User, $SendUser, $ChatID, $Message, $MessageID);
        // }
    }

    public function Group($User, $SendUser, $ChatID, $Message, $MessageID)
    {
        return $this->replyWithMessage(
            [
                'text'                  => 'Group',
                'parse_mode'            => 'Markdown',
                'reply_to_message_id'   => $MessageID,
            ]
        );
    }

    public function Privacy($User, $SendUser, $ChatID, $Message, $MessageID)
    {
        return $this->replyWithMessage(
            [
                'text'                  => 'Privacy',
                'parse_mode'            => 'Markdown',
                'reply_to_message_id'   => $MessageID,
            ]
        );
    }

    public function reply($Message, $MessageID)
    {
        return $this->replyWithMessage(
            [
                'text'                  => $Message,
                'parse_mode'            => 'Markdown',
                'reply_to_message_id'   => $MessageID,
            ]
        );
    }

    /**
     *  分割字符串
     *
     * @param string $Str       源字符串
     * @param string $Delimiter 分割定界符
     * @param int    $Quantity  最大返回数量
     *
     * @return array
     */
    public function StrExplode($Str, $Delimiter, $Quantity = 10)
    {
        $return = [];
        $Str = trim($Str);
        for ($x = 0; $x <= 10; $x++) {
            if (strpos($Str, $Delimiter) !== false && count($return) < $Quantity - 1) {
                $temp = substr($Str, 0, strpos($Str, $Delimiter));
                $return[] = $temp;
                $Str = trim(substr($Str, strlen($temp)));
            } else {
                $return[] = $Str;
                break;
            }
        }
        return $return;
    }

    /**
     * 查找字符串是否是某个方法的别名
     *
     * @param array  $MethodGroup 方法别名的数组
     * @param string $Search      被搜索的字符串
     *
     * @return string
     */
    public function getOptionMethod($MethodGroup, $Search)
    {
        $useMethod = '';
        foreach ($MethodGroup as $MethodName => $Remarks) {
            if (strlen($MethodName) === strlen($Search)) {
                if (stripos($MethodName, $Search) === 0) {
                    $useMethod = $MethodName;
                    break;
                }
            }
            if (count($Remarks) >= 1) {
                foreach ($Remarks as $Remark) {
                    if (strlen($Remark) === strlen($Search) && stripos($Remark, $Search) === 0) {
                        $useMethod = $MethodName;
                        break 2;
                    }
                }
            }
        }
        return $useMethod;
    }

    /**
     * 使用 $Value 给定的运算式与 $Source 计算结果
     *
     * @param string $Source         源数值
     * @param string $Value          运算式含增改数值
     * @param bool   $FloatingNumber 是否格式化为浮点数
     *
     * @return string|null
     */
    public function ComputingMethod($Source, $Value, $FloatingNumber = false)
    {
        if (
            (strpos($Value, '+') === 0
                ||
                strpos($Value, '-') === 0
                ||
                strpos($Value, '*') === 0
                ||
                strpos($Value, '/') === 0)
            &&
            is_numeric(substr($Value, 1))
        ) {
            $Source = eval('return $Source ' . substr($Value, 0, 1) . '= ' . substr($Value, 1) . ';');
        } else {
            if (is_numeric($Value)) {
                $Source = $Value;
            } else {
                $Source = null;
            }
        }
        if ($Source !== null) {
            $Source = ($FloatingNumber === false
                ? number_format($Source, 0, '.', '')
                : number_format($Source, 2, '.', ''));
        }
        return $Source;
    }

    /**
     * 使用 $Value 给定的运算式及流量单位与 $Source 计算结果
     *
     * @param string $Source 源数值
     * @param string $Value  运算式含增改数值
     *
     * @return int|null
     */
    public function TrafficMethod($Source, $Value)
    {
        if (
            strpos($Value, '+') === 0
            ||
            strpos($Value, '-') === 0
            ||
            strpos($Value, '*') === 0
            ||
            strpos($Value, '/') === 0
        ) {
            $operator = substr($Value, 0, 1);
            if (!in_array($operator, ['*', '/'])) {
                $number = Tools::flowAutoShowZ(substr($Value, 1));
            } else {
                $number = substr($Value, 1, strlen($Value) - 1);
                if (!is_numeric($number)) return null;
            }
            if ($number === null) {
                return null;
            }
            $Source = eval('return $Source ' . $operator . '= ' . $number . ';');
        } else {
            if (is_numeric($Value)) {
                if ((int) $Value === 0) {
                    $Source = 0;
                } else {
                    $Source = Tools::flowAutoShowZ($Value . 'KB');
                }
            } else {
                $Source = Tools::flowAutoShowZ($Value);
            }
        }
        return $Source;
    }
}
