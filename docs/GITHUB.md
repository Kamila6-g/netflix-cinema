# Публикация проекта на GitHub

Проект опубликован: **https://github.com/Kamila6-g/netflix-cinema** (ветка `main`).
Ниже — последовательность команд, которой проект был опубликован и которой
проверяется цикл «изменил → commit → push → изменение видно в GitHub Desktop».

Локальный репозиторий инициализирован и содержит коммиты (см. `git log`),
удалённый `origin` привязан к репозиторию выше.

---

## 1. Репозиторий на GitHub

Репозиторий уже создан: `https://github.com/Kamila6-g/netflix-cinema` (Public).
Если проект публикуется заново под другим аккаунтом:

1. Войти в аккаунт на https://github.com.
2. **New repository** → имя, например `netflix-cinema` → **Public** или **Private**.
3. **Не** добавлять README, `.gitignore` и лицензию (они уже есть в проекте).
4. Скопировать URL вида `https://github.com/<логин>/netflix-cinema.git`.

---

## 2. Привязать удалённый репозиторий и отправить код

Открыть PowerShell в папке проекта:

```powershell
cd C:\OSPanel\domains\localhost\NETFLIX

# Проверить, что история на месте
git log --oneline

# Привязать удалённый репозиторий (заменить <логин> и имя)
git remote add origin https://github.com/<логин>/netflix-cinema.git

# Переименовать ветку в main (если ещё master)
git branch -M main

# Первая публикация
git push -u origin main
```

Если Git попросит пароль — используется **Personal Access Token** (GitHub → Settings →
Developer settings → Personal access tokens → Tokens (classic) → Generate new token,
права `repo`). Пароль аккаунта для HTTPS больше не подходит.

---

## 3. Показать цикл изменений

```powershell
# 1. Изменить любой файл, например README.md (добавить строку)
# 2. Посмотреть, что изменилось
git status
git diff

# 3. Закоммитить
git add README.md
git commit -m "README: уточнение инструкции запуска"

# 4. Отправить на GitHub
git push
```

5. Открыть **GitHub Desktop** → **File → Add local repository** → выбрать
   `C:\OSPanel\domains\localhost\NETFLIX`.
6. На вкладке **History** появится новый коммит, а кнопка **Push origin** отправит его.
7. Обновить страницу репозитория на GitHub — изменение видно в веб-интерфейсе.
   Это и есть требуемая демонстрация: изменил файл → commit → push → изменение в GitHub Desktop.

---

## 4. Что не попадает в репозиторий

Список исключений — в `.gitignore`:

- `.env`, `config/config.local.php` — локальные настройки и секреты;
- `*.log`, `logs/` — логи;
- `node_modules/`, `vendor/`, `dist/`, `build/` — зависимости и сборка;
- тяжёлые медиа: `*.mp4`, `*.mov`, `*.mkv`;
- старые дампы `cinema_db*.sql` (актуальный дамп — `db.sql`);
- служебные файлы IDE и ОС.

Проверить, что секреты не попали в индекс:

```powershell
git ls-files | Select-String -Pattern "\.env|config\.local"
```

Команда не должна ничего вывести.

---

## 5. Если репозиторий уже создан ранее

```powershell
git remote -v                      # посмотреть текущий remote
git remote set-url origin <URL>    # заменить URL
git push -u origin main
```

Если ветки расходятся:

```powershell
git pull --rebase origin main
git push
```
