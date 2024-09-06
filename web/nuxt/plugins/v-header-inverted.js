class Header {
    constructor() {
        this.observer;
        this.observerInvertedNodes = [];
        this.header;
    }

    init(headerNode) {
        this.header = headerNode;

        window.addEventListener('resize', () => this.resetObserver());
        this.resetObserver();
    }

    resetObserver() {
        if (this.observer) {
            this.observer.disconnect();
        }

        const { top, height } = this.header.getBoundingClientRect();

        this.observer = new IntersectionObserver((entries) => this.observerCallback(entries), {
            root: document,
            rootMargin: `-${top}px 0px -${window.innerHeight - top - height}px 0px`
        });

        if (this.observerInvertedNodes && this.observerInvertedNodes.length) {
            this.observerInvertedNodes.forEach((el) => {
                this.observer.observe(el);
            });
        }
    }

    observerCallback(entries) {
        let inverted = false;
        entries.forEach(({ isIntersecting }) => {
            if (isIntersecting) {
                inverted = true;
            }
        });
        if (inverted) {
            this.header.classList.add('is-invert');
        } else {
            this.header.classList.remove('is-invert');
        }
    }
}

const HeaderObserver = new Header();
export default defineNuxtPlugin(({ vueApp }) => {
    // Дериктива для щапки
    vueApp.directive('header', {
        mounted(el) {
            HeaderObserver.init(el);
        }
    });

    // Деректива для элементов, на которых делаем инверсию
    vueApp.directive('header-inverted', {
        mounted(el) {
            HeaderObserver.observerInvertedNodes.push(el);
            HeaderObserver.observer.observe(el);
        },

        beforeUnmount(el) {
            if (HeaderObserver.observerInvertedNodes && HeaderObserver.observerInvertedNodes.length) {
                HeaderObserver.observerInvertedNodes = HeaderObserver.observerInvertedNodes.filter(
                    (node) => !node.isEqualNode(el)
                );
            }
            HeaderObserver.observer.unobserve(el);
        }
    });
});
