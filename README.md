# tp6-captcha

thinkphp6 验证码类库

```text
- 基于 ThinkPHP 6.0.x 编写
- 测试环境为 ThinkPHP 6.0.1
- 必备php扩展 Imagick
```

## 安装

> composer require linjialiang/tp6-captcha

## 使用

### 在控制器中输出验证码

在控制器的操作方法中使用

```
public function captcha($id = '')
{
	return captcha($id);
}
```

然后注册对应的路由来输出验证码

### 模板里输出验证码

首先要在你应用的路由定义文件中，注册一个验证码路由规则。

```
\linjialiang\facade\Route::get('captcha/[:id]', "\\linjialiang\\captcha\\CaptchaController@index");
```

然后就可以在模板文件中使用

```
<div>{:captcha_img()}</div>
```

或者

```
<div><img src="{:captcha_src()}" alt="captcha" /></div>
```

> 上面两种的最终效果是一样的

### 控制器里验证

使用 TP 的内置验证功能即可

```
$this->validate($data,[
    'captcha|验证码'=>'require|captcha'
]);
```

或者手动验证

```
if(!captcha_check($captcha)){
 //验证失败
};
```

## 字符说明

默认字体，支持的字符有限，如需增加其它字符请重新载入字体：

| 目录            | 描述       |
| --------------- | ---------- |
| assets/fonts    | 常规字符集 |
| assets/fonts@zh | 中文字符集 |
| assets/math     | 算术字符集 |

支持 3 种类型的字体： `ttf` 、 `woff` 、 `woff2`

### 字符集内容：

1. 中文字符集

    ```text
    天地玄黄宇宙洪荒日月列张寒来暑往秋收冬闰余成岁律吕调阳云腾致雨结为金生丽水玉出昆冈剑号巨珠称夜光果珍李重姜海咸河淡羽翔龙师火帝鸟官人皇始制文字乃服衣裳推位让国有唐吊民伐罪周发汤坐朝问道垂拱平章爱育
    ```

2. 常规字符集

    ```text
    2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY
    ```

3. math 字符集

    ```text
    0123456789+-*/%
    ```

## 参考来源

tp6-captcha 灵感来自 [thinkphp 官方验证码类库](https://packagist.org/packages/topthink/think-captcha)，操作上与其基本一致！
