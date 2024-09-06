## Setup

Make sure to install the dependencies:

```bash
# npm
npm install
```

## Development Server

Start the development server on http://localhost:3000

```bash
npm run dev
```

## Доступ к API

Используется composables  
В любом месте можно вызвать функцию: `useFetchApi()`, это практически полный аналог `useFetch()`, за исключением:

-   Базовый домен настроен на API сервер (docker/.env)
-   Настроен stringify для параметров (axios like)

## Корзина

Используется composables  
Корзина грузится при загрузке страницы (монтировании приложения, а не переходах по страницам)  
В любом месте можно вызвать текущую корзину через: `useBasket()`  
Например (другие примеры смотрите в реализации):

```ts
let productId = 123;
const basket = useBasket();
await basket.add(productId); // Добавить в коризну (Promise)
await basket.delete(productId); // Удалить из корзины (Promise)
```
