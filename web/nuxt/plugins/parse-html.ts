// Создаем новый скрипт элемент на основе существующего
function createScriptElement(script: HTMLScriptElement): Promise<HTMLScriptElement> {
    return new Promise((resolve, reject) => {
        const newScript = document.createElement('script');
        newScript.type = script.type || 'text/javascript'; // устанавливаем type нового элемента
        if (!script.textContent) {
            newScript.src = script.src; // устанавливаем ссылку на внешний скрипт нового элемента
        }
        if (script.textContent) {
            newScript.textContent = script.textContent; // устанавливаем содержимое нового элемента, если оно есть
            resolve(newScript);
        } else {
            // Обработчик события load для скрипта
            newScript.onload = () => resolve(newScript); // разрешаем промис после загрузки скрипта
            newScript.onerror = (error) => reject(error); // отклоняем промис в случае ошибки загрузки скрипта
            document.body.appendChild(newScript); // TODO: убрать из тела
        }
    });
}

// Функция, проверяющая, является ли переданный узел скриптом
function isScriptElement(node: Node): node is HTMLScriptElement {
    return node.nodeType === Node.ELEMENT_NODE && (node as HTMLElement).tagName === 'SCRIPT';
}

// Функция рекурсивного обхода DOM дерева и парсинга узлов
async function parseNode(node: Node): Promise<Node | Node[]> {
    // Если узел является текстовым узлом, возвращаем его без изменений
    if (node.nodeType === Node.TEXT_NODE) {
        return node;
    }
    // Если узел является скриптом, создаем новый скрипт элемент и возвращаем его в массиве
    else if (isScriptElement(node)) {
        const newScript = await createScriptElement(node);
        return [newScript];
    }
    // Если узел является элементом, создаем новый элемент и заполняем его атрибутами и дочерними элементами
    else {
        const elementNode = node as HTMLElement;
        let nodeName = 'DIV';
        if (!['HTML', 'BODY', 'HEAD'].includes(elementNode.nodeName)) {
            nodeName = elementNode.nodeName;
        }

        // Комментарии
        if (nodeName === '#comment') {
            const newElement = document.createElement('DIV');
            return newElement;
        }

        const newElement = document.createElement(nodeName);

        // Заполняем атрибуты нового элемента атрибутами старого элемента
        Array.from(elementNode.attributes).forEach((attr) => newElement.setAttribute(attr.name, attr.value));
        // Заполняем дочерние элементы нового элемента дочерними узлами старого элемента
        const childNodes = Array.from(elementNode.childNodes);
        for (const childNode of childNodes) {
            const parsedNode = await parseNode(childNode);
            if (Array.isArray(parsedNode)) {
                for (const parsedChildNode of parsedNode) {
                    newElement.appendChild(parsedChildNode);
                }
            } else {
                newElement.appendChild(parsedNode);
            }
        }
        return newElement;
    }
}

export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.vueApp.directive('parse-html', {
        async mounted(el, binding) {
            // Создаем новый парсер
            const parser = new DOMParser();
            // Получаем HTML код из директивы
            const html = binding.value;
            // Парсим HTML код в DOM дерево
            const doc = parser.parseFromString(html, 'text/html');

            const childNodes = Array.from(doc.childNodes);

            const parsedChildNodes = await Promise.all(childNodes.map(parseNode));
            parsedChildNodes.flat().forEach((parsedChildNode) => el.appendChild(parsedChildNode));
        }
    });
});
