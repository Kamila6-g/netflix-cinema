/**
 * assets/js/main.js — клиентская логика сайта.
 *
 * Блоки:
 *   1. Мобильное меню и «прилипающая» шапка;
 *   2. Простая проверка обязательных полей (формы входа);
 *   3. AJAX-регистрация с сообщениями об ошибках без перезагрузки страницы;
 *   4. Переключение сезонов на странице сериала.
 *
 * Данные всегда повторно проверяются на сервере: JS нужен только для удобства.
 */

document.addEventListener('DOMContentLoaded', () => {
  /* ------------------------------------------------------------------
   * 1. Мобильное меню и шапка
   * ------------------------------------------------------------------ */
  const burger = document.getElementById('burger');
  const nav = document.getElementById('nav');

  if (burger && nav) {
    burger.addEventListener('click', () => {
      const opened = nav.classList.toggle('open');
      burger.setAttribute('aria-expanded', opened ? 'true' : 'false');
    });
  }

  const header = document.getElementById('header');
  if (header) {
    const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 40);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ------------------------------------------------------------------
   * 2. Проверка обязательных полей (формы с атрибутом data-validate)
   * ------------------------------------------------------------------ */
  document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      let valid = true;

      form.querySelectorAll('[required]').forEach((input) => {
        const field = input.closest('.field');
        if (!field) return;
        if (!input.value.trim()) {
          field.classList.add('invalid');
          valid = false;
        } else {
          field.classList.remove('invalid');
        }
      });

      // Совпадение паролей — только для форм, где есть оба поля.
      const pass = form.querySelector('input[name="password"]');
      const pass2 = form.querySelector('input[name="password2"]');
      if (pass && pass2 && pass.value !== pass2.value) {
        pass2.closest('.field')?.classList.add('invalid');
        valid = false;
      }

      if (!valid) event.preventDefault();
    });
  });

  /* ------------------------------------------------------------------
   * 3. AJAX-регистрация (register.php)
   * ------------------------------------------------------------------ */
  const regForm = document.querySelector('form[data-register]');

  if (regForm) {
    const general = document.getElementById('formGeneral');

    /** Показывает текст ошибки под конкретным полем. */
    const setFieldError = (name, message) => {
      const box = regForm.querySelector(`.field [data-error-for="${name}"]`);
      if (!box) return;
      const field = box.closest('.field');
      box.textContent = message;
      field.classList.add('invalid');
    };

    const clearErrors = () => {
      regForm.querySelectorAll('.field').forEach((f) => f.classList.remove('invalid'));
      if (general) {
        general.textContent = '';
        general.classList.add('hidden');
      }
    };

    const showGeneral = (message) => {
      if (!general) return;
      general.textContent = message;
      general.classList.remove('hidden');
    };

    /** Клиентская проверка: те же правила, что и на сервере. */
    const validateClient = () => {
      let ok = true;
      const username = regForm.username.value.trim();
      const email = regForm.email.value.trim();
      const password = regForm.password.value;
      const password2 = regForm.password2.value;

      if (!/^[A-Za-zА-Яа-яЁё0-9_-]{3,30}$/.test(username)) {
        setFieldError('username', 'Имя: 3–30 символов, буквы, цифры, «_» или «-».');
        ok = false;
      }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
        setFieldError('email', 'Введите корректный email.');
        ok = false;
      }
      if (password.length < 8 || !/[A-Za-zА-Яа-яЁё]/.test(password) || !/\d/.test(password)) {
        setFieldError('password', 'Минимум 8 символов, буквы и хотя бы одна цифра.');
        ok = false;
      }
      if (password !== password2) {
        setFieldError('password2', 'Пароли не совпадают.');
        ok = false;
      }
      return ok;
    };

    regForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      clearErrors();

      if (!validateClient()) return;

      const button = regForm.querySelector('button[type="submit"]');
      if (button) {
        button.disabled = true;
        button.textContent = 'Создаём аккаунт…';
      }

      try {
        const response = await fetch('register.php', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: new FormData(regForm),
        });

        const data = await response.json();

        if (data.ok) {
          window.location.href = data.redirect || 'profile.php';
          return;
        }

        Object.entries(data.errors || {}).forEach(([name, message]) => setFieldError(name, message));
        if (data.general) showGeneral(data.general);
      } catch (error) {
        showGeneral('Не удалось отправить форму. Проверьте соединение и попробуйте ещё раз.');
      } finally {
        if (button) {
          button.disabled = false;
          button.textContent = 'Создать аккаунт';
        }
      }
    });
  }

  /* ------------------------------------------------------------------
   * 4. Сезоны и эпизоды на странице сериала
   * ------------------------------------------------------------------ */
  document.querySelectorAll('.season-tab').forEach((tab) => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.season-tab').forEach((t) => t.classList.remove('active'));
      tab.classList.add('active');

      document.querySelectorAll('.season-block').forEach((block) => {
        block.style.display = 'none';
      });

      const target = document.querySelector(`.season-block[data-season="${tab.dataset.season}"]`);
      if (target) target.style.display = 'grid';
    });
  });
});
