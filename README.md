## Установка

```
composer require symfony/beanstalk-bundle 
```

## Использование
Данный бандл не добавляет ни файлы конфигурации ни регистрацию бандла и т.д. т.к. его создатель
не умеет использовать должным образом symfony-flex.


Регистрация бандла в ```config/bundles.php```:
```php
return [
    ... 
    Symfony\BeanstalkBundle\BeanstalkBundle::class => ['all' => true],
];
```


Конфигурация бандла в ```packages/beanstalk.yaml```

```yaml
beanstalk:
  processor:
    dsn: transport://host:port

```

Бандл позволяет зарегистрировать собственный(других нет) обработчик
 ошибок возникающих при обработке т.н. джобов:
```yaml
beanstalk:
  ...
  on_error:
    listener: Твой\Неймспейс\ТвойКласс
``` 
здесь "ТвойКласс" должен реализовывать интерфейс ```Symfony\BeanstalkBundle\IErrorListener```

Далее необходимо создать объекты, наследующие следующие абстрактные классы:
1. Symfony\BeanstalkBundle\AConsumer;
2. Symfony\BeanstalkBundle\APayload.
Пример нагрузки:
```php
<?php
namespace App\Payload;

use Symfony\BeanstalkBundle\APayload;

class MailTemplatingPayload extends APayload
{
	public $from;
	public $to;
	public $context;
	public $template;
}
```
Пример консюмера:
```php
<?php
namespace App\Consumer;

use Pheanstalk\Job;
use Swift_Mailer;
use App\Payload\MailTemplatingPayload;
use Symfony\BeanstalkBundle\AConsumer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class MailConsumer extends AConsumer
{
	/**
	 * @var Swift_Mailer
	 */
	private $mailer;

	public function __construct(MailerInterface $mailer)
	{
		$this->mailer = $mailer;
	}

	public function tubeName(): string
	{
		return 'mails';
	}

	public function execute(Job $job)
	{
		/** @var MailTemplatingPayload $payload */
		$payload = $this->extract($job, MailTemplatingPayload::class);

		$email = (new TemplatedEmail())
			->from($payload->from)
			->to($payload->to)
			->htmlTemplate($payload->template)
			->context($payload->context);

		$this->mailer->send($email);
	}
}
```

В ```config/services.yaml``` добавь:
```yaml
beanstalk.mail_consumer:
    class: App\Consumer\MailConsumer
    public: true
``` 
>Обрати внимание, сервис должен быть публичным для возможности дернуть его из контейнера symfony

Добавление задания в очередь:
```php
class HomeController extends AbstractController
{
	/**
	 * @Route("/", name="home")
	 * @param AProducer $producer
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function home(AProducer $producer)
	{
		$payload = new MailTemplatingPayload();
		$payload->from = 'admin@mail.ru';
		$payload->to = 'n_ds@mail.ru';
		$payload->template = 'email/hello.html.twig';
		$payload->context = [ 'name' => 'Jon'];
		$producer->setTube('mails')->publish($payload);
		return $this->render('home/index.html.twig');
	}
}
```
Старт очереди:
```php
bin/console consumer:listen твой_id_сервиса_консюмера_в_контейнере
```
>По дефолту консумер выполняет максимум 10 работ и умирает.
В общем необходимо позаботиться об перезагрузке консумера, например супервизором или чем там обычно перезапускаешь службы.

## На почитать
Под капотом данный бандл использует https://packagist.org/packages/pda/pheanstalk 
