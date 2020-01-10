<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace linjialiang\captcha;

use Exception;
use think\Config;
use think\Response;
use think\Session;

class Captcha
{
    /**
     * @var Config|null
     */
    private $config = null;

    /**
     * @var Session|null
     */
    private $session = null;

    // 验证码实例
    private $im = null;
    // 验证码渲染实例
    private $draw = null;

    // 验证码颜色
    protected string $color = '';
    // 验证码字符集合
    protected string $codeSet = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
    // 验证码过期时间（s）
    protected int $expire = 1800;
    // 使用中文验证码
    protected bool $useZh = false;
    // 中文验证码字符串
    protected string $zhSet = '赵钱孙李周吴郑王冯陈褚卫蒋沈韩杨朱秦尤许何吕施张孔曹严华金魏陶姜戚谢邹喻柏水窦章云苏潘葛奚范彭郎鲁韦昌马苗凤花方俞任袁柳酆鲍史唐费廉岑薛雷贺倪汤滕殷罗毕郝邬安常乐于时傅皮卞齐康伍余元卜顾孟平黄和穆萧尹姚邵湛汪祁毛禹狄米贝明臧计伏成戴谈宋茅庞熊纪舒屈项祝董梁杜阮蓝闵席季麻强贾路娄危江童颜郭梅盛林刁钟徐邱骆高夏蔡田樊胡凌霍虞万支柯昝管卢莫经房裘缪干解应宗丁宣贲邓郁单杭洪包诸左石崔吉钮龚程嵇邢滑裴陆荣翁荀羊於惠甄曲家封芮羿储靳汲邴糜松井段富巫乌焦巴弓牧隗山谷车侯宓蓬全郗班仰秋仲伊宫宁仇栾暴甘钭厉戎祖武符刘景詹束龙叶幸司韶郜黎蓟薄印宿白怀蒲邰从鄂索咸籍赖卓蔺屠蒙池乔阴胥能苍双闻莘党翟谭贡劳逄姬申扶堵冉宰郦雍璩桑桂濮牛寿通边扈燕冀郏浦尚农温别庄晏柴瞿阎充慕连茹习宦艾鱼容向古易慎戈廖庾终暨居衡步都耿满弘匡国文寇广禄阙东欧殳沃利蔚越夔隆师巩厍聂晁勾敖融冷訾辛阚那简饶空曾毋沙乜养鞠须丰巢关蒯相查后荆红游竺权逯盖益桓公万俟司马上官欧阳夏侯诸葛闻人东方赫连皇甫尉迟公羊澹台公冶宗政濮阳淳于单于太叔申屠公孙仲孙轩辕令狐钟离宇文长孙慕容鲜于闾丘司徒司空丌官司寇仉督子车颛孙端木巫马公西漆雕乐正壤驷公良拓跋夹谷宰父谷梁晋楚闫法汝鄢涂钦段干百里东郭南门呼延归海羊舌微生岳帅缑亢况有琴梁丘左丘东门西门商牟佘佴伯赏南宫墨哈谯笪年爱阳佟第五言福';
    // 使用背景图片
    protected bool $useImgBg = false;
    // 验证码字体大小(px)
    protected int $fontSize = 25;
    // 是否画混淆曲线
    protected bool $useCurve = false;
    // 是否添加杂点
    protected bool $useNoise = false;
    // 验证码图片高度
    protected int $imageH = 0;
    // 验证码图片宽度
    protected int $imageW = 0;
    // 验证码位数
    protected int $length = 5;
    // 验证码字体，不设置随机获取
    protected string $fontttf = '';
    // 背景颜色
    protected string $bg = '';
    // 算术验证码
    protected bool $math = false;
    // 随机运算符号，支持加(+)、减(-)、乘(*)、除(/)、取模(%)5种运算
    protected array $operators = [];

    /**
     * 架构方法 设置参数
     * @access public
     * @param Config  $config
     * @param Session $session
     */
    public function __construct(Config $config, Session $session)
    {
        $this->config  = $config;
        $this->session = $session;
    }

    /**
     * 配置验证码
     * @param string|null $config
     */
    protected function configure(string $config = null): void
    {
        if (is_null($config)) {
            $config = $this->config->get('captcha', []);
        } else {
            $config = $this->config->get('captcha.' . $config, []);
        }

        foreach ($config as $key => $val) {
            if (property_exists($this, $key)) {
                $this->{$key} = $val;
            }
        }
    }

    /**
     * 创建验证码
     * @return array
     * @throws Exception
     */
    protected function generate(): array
    {
        $bag = '';

        if ($this->math) {
            $this->useZh  = false;
            $this->length = 5;

            $x   = random_int(10, 30);
            $y   = random_int(1, 9);

            switch ($this->operators ? $this->operators[array_rand($this->operators)] : '+') {
                case '-':
                    $bag = "{$x} - {$y} = ";
                    $key = $x - $y;
                    break;
                case '*':
                    $bag = "{$x} * {$y} = ";
                    $key = $x * $y;
                    break;
                case '/':
                    $x = mt_rand(1, 10) * $y;
                    $bag = "{$x} / {$y} = ";
                    $key = $x / $y;
                    break;
                case '%':
                    $bag = "{$x} % {$y} = ";
                    $key = $x % $y;
                    break;
                default:
                    $bag = "{$x} + {$y} = ";
                    $key = $x + $y;
                    break;
            }

            $key .= '';
        } else {
            if ($this->useZh) {
                $characters = preg_split('/(?<!^)(?!$)/u', $this->zhSet);
            } else {
                $characters = str_split($this->codeSet);
            }

            for ($i = 0; $i < $this->length; $i++) {
                $bag .= $characters[rand(0, count($characters) - 1)];
            }

            $key = mb_strtolower($bag, 'UTF-8');
        }

        $hash = password_hash($key, PASSWORD_BCRYPT, ['cost' => 10]);

        $this->session->set('captcha', [
            'key' => $hash,
        ]);

        return [
            'value' => $bag,
            'key'   => $hash,
        ];
    }

    /**
     * 验证验证码是否正确
     * @access public
     * @param string $code 用户验证码
     * @return bool 用户验证码是否正确
     */
    public function check(string $code): bool
    {
        if (!$this->session->has('captcha')) {
            return false;
        }

        $key = $this->session->get('captcha.key');

        $code = mb_strtolower($code, 'UTF-8');

        $res = password_verify($code, $key);

        if ($res) {
            $this->session->delete('captcha');
        }

        return $res;
    }

    /**
     * 输出验证码并把验证码的值保存的session中
     * @access public
     * @param null|string $config
     * @param bool        $api
     * @return Response
     */
    public function create(string $config = null, bool $api = false): Response
    {
        $this->configure($config);

        // 图片宽(px)
        $this->imageW || $this->imageW = $this->length * $this->fontSize * 1.5 + $this->length * $this->fontSize / 2;
        // 图片高(px)
        $this->imageH || $this->imageH = $this->fontSize * 2.5;

        if ($this->useImgBg) {
            $path = __DIR__ . '/../assets/bgs/';
            $dir  = dir($path);
            $bgs = [];
            while (false !== ($file = $dir->read())) {
                if ('.' != $file[0] && substr($file, -4) == '.png') {
                    $bgs[] = $path . $file;
                }
            }
            $dir->close();
            $gb = $bgs[array_rand($bgs)];
            $this->im = new \Imagick($gb);
            $this->im->resizeImage($this->imageW, $this->imageH, \Imagick::FILTER_POINT, 1);
        } else {
            $this->im =  new \Imagick();
            // 建立一幅大小为 $this->imageW * $this->imageH 的画布，背景色为 $this->bg
            $this->im->newImage($this->imageW, $this->imageH, $this->bg ? $this->bg : 'rgba(243, 251, 254, 1)');
        }

        $this->draw = new \ImagickDraw();

        // 验证码使用随机字体
        $ttfPath = __DIR__ . '/../assets/' . ($this->math ? 'mathttfs' : ($this->useZh ? 'zhttfs' : 'ttfs')) . '/';

        if (empty($this->fontttf)) {
            $dir  = dir($ttfPath);
            $ttfs = [];
            while (false !== ($file = $dir->read())) {
                if ('.' != $file[0] && substr($file, -4) == '.ttf') {
                    $ttfs[] = $file;
                }
            }
            $dir->close();
            $this->fontttf = $ttfs[array_rand($ttfs)];
        }

        // 字体全路径
        $fontttf = $ttfPath . $this->fontttf;
        // 设置验证码字体
        $this->draw->setFont($fontttf);
        // 设置验证码字体大小
        $this->draw->setFontSize($this->fontSize);

        // 获取创建好的验证码
        $generator = $this->generate();
        // 将验证码从字符串转成数组
        $text = $this->useZh ? preg_split('/(?<!^)(?!$)/u', $generator['value']) : str_split($generator['value']);

        // 将验证码字符，挨个画出来
        foreach ($text as $index => $char) {
            $x     = $this->fontSize * ($index + 1) * mt_rand(1.2, 1.6) * ($this->math ? 1 : 1.5);
            $y     = $this->fontSize + mt_rand(10, 20);
            $angle = $this->math ? 0 : mt_rand(-20, 20);

            // 验证码文字以及坐标
            $this->draw->annotation($x, $y, $char);
            // 验证码文字在x轴上的倾斜角度
            $this->draw->skewX($angle);
            // 验证码文字颜色
            if (!$this->color) {
                $this->draw->setFillColor(
                    'rgb(' .
                        mt_rand(1, 150)
                        . ',' .
                        mt_rand(1, 150)
                        . ',' .
                        mt_rand(1, 150)
                        . ')'
                );
            } else {
                $this->draw->setFillColor($this->color);
            }
        }

        if ($this->useCurve) {
            // 绘干扰线
            $this->writeCurve();
        }

        if ($this->useNoise) {
            // 绘杂点
            $this->writeNoise();
        }

        // 验证码输出格式
        $this->im->setImageFormat("png");
        // 验证码最终输出的样式
        $this->im->drawImage($this->draw);
        ob_start();
        // 输出图像
        echo $this->im;
        $content = ob_get_clean();
        // 销毁imagick对象
        $this->im->destroy();
        return response($content, 200, ['Content-Length' => strlen($content)])->contentType('image/png');
    }

    /**
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数)
     *
     *      高中的数学公式咋都忘了涅，写出来
     *        正弦型函数解析式：y=Asin(ωx+φ)+b
     *      各常数值对函数图像的影响：
     *        A：决定峰值（即纵向拉伸压缩的倍数）
     *        b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *        φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *        ω：决定周期（最小正周期T=2π/∣ω∣）
     *
     */
    protected function writeCurve(): void
    {
        // imagick 画贝塞尔曲线
        $px = $py = 0;
        // 文本随机颜色（验证码颜色）
        $this->draw->setFillColor(
            'rgb(' .
                mt_rand(150, 225)
                . ',' .
                mt_rand(150, 225)
                . ',' .
                mt_rand(150, 225)
                . ')'
        );

        // 曲线前部分
        $A = mt_rand(1, $this->imageH / 2); // 振幅
        $b = mt_rand(-$this->imageH / 4, $this->imageH / 4); // Y轴方向偏移量
        $f = mt_rand(-$this->imageH / 4, $this->imageH / 4); // X轴方向偏移量
        $T = mt_rand($this->imageH, $this->imageW * 2); // 周期
        $w = (2 * M_PI) / $T;

        $px1 = 0; // 曲线横坐标起始位置
        $px2 = mt_rand($this->imageW / 2, $this->imageW * 0.8); // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = $A * sin($w * $px + $f) + $b + $this->imageH / 2; // y = Asin(ωx+φ) + b
                $i  = (int) ($this->fontSize / 5);
                $coord = [];    // 坐标
                while ($i > 0) {
                    $coord[] = ['x' => $px + $i, 'y' => $py + $i];
                    $i--;
                }
                $this->draw->bezier($coord);
            }
        }

        // 曲线后部分
        $A   = mt_rand(1, $this->imageH / 2); // 振幅
        $f   = mt_rand(-$this->imageH / 4, $this->imageH / 4); // X轴方向偏移量
        $T   = mt_rand($this->imageH, $this->imageW * 2); // 周期
        $w   = (2 * M_PI) / $T;
        $b   = $py - $A * sin($w * $px + $f) - $this->imageH / 2;
        $px1 = $px2;
        $px2 = $this->imageW;

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = $A * sin($w * $px + $f) + $b + $this->imageH / 2; // y = Asin(ωx+φ) + b
                $i  = (int) ($this->fontSize / 5);
                $coord = [];    // 坐标
                while ($i > 0) {
                    $coord[] = ['x' => $px + $i, 'y' => $py + $i];
                    $i--;
                }
                $this->draw->bezier($coord);
            }
        }
    }

    /**
     * 画杂点
     * 往图片上写不同颜色的文字
     */
    protected function writeNoise(): void
    {
        $bag = '';

        if (!$this->math && $this->useZh) {
            $characters = preg_split('/(?<!^)(?!$)/u', $this->zhSet);
        } else {
            $characters = str_split($this->codeSet);
        }

        for ($i = 0; $i < 30; $i++) {
            $bag .= $characters[rand(0, count($characters) - 1)];
        }

        $key = mb_strtolower($bag, 'UTF-8');

        $text = $this->useZh ? preg_split('/(?<!^)(?!$)/u', $key) : str_split($key);

        foreach ($text as $index => $char) {
            // 文本字体大小
            $this->draw->setFontSize($this->fontSize * 4 / 5);
            // 文本随机颜色（验证码颜色）
            $this->draw->setFillColor(
                'rgba(' .
                    mt_rand(150, 225)
                    . ',' .
                    mt_rand(150, 225)
                    . ',' .
                    mt_rand(150, 225)
                    . ',' .
                    mt_rand(5, 8) / 10
                    . ')'
            );
            // 图片上插入随机文本（验证码）
            $this->draw->annotation(mt_rand(-10, $this->imageW), mt_rand(-10, $this->imageH), $char);
        }
    }
}
