import { translations, SUPPORTED_LANGS, DEFAULT_LANG } from './translations.js';

const STORAGE_KEY = 'sl_lang';

function getFromPath(obj, path) {
  return path.split('.').reduce((acc, part) => (acc == null ? acc : acc[part]), obj);
}

export function getCurrentLang() {
  const saved = localStorage.getItem(STORAGE_KEY);
  if (saved && SUPPORTED_LANGS.includes(saved)) return saved;

  const browserLang = (navigator.language || '').slice(0, 2);
  if (SUPPORTED_LANGS.includes(browserLang)) return browserLang;

  return DEFAULT_LANG;
}

function applyTranslations(lang) {
  const dict = translations[lang] || translations[DEFAULT_LANG];

  document.querySelectorAll('[data-i18n]').forEach((el) => {
    const value = getFromPath(dict, el.getAttribute('data-i18n'));
    if (value != null) el.textContent = value;
  });

  // Attributs traduisibles : data-i18n-attr="placeholder:ma.cle|title:autre.cle"
  document.querySelectorAll('[data-i18n-attr]').forEach((el) => {
    el.getAttribute('data-i18n-attr').split('|').forEach((pair) => {
      const [attr, key] = pair.split(':');
      const value = getFromPath(dict, key);
      if (attr && value != null) el.setAttribute(attr, value);
    });
  });

  document.documentElement.setAttribute('lang', lang);

  document.querySelectorAll('[data-lang]').forEach((btn) => {
    btn.classList.toggle('lang-active', btn.getAttribute('data-lang') === lang);
  });

  const currentLabel = document.getElementById('lang-current');
  if (currentLabel) currentLabel.textContent = lang.toUpperCase();
}

export function setLang(lang) {
  if (!SUPPORTED_LANGS.includes(lang)) return;
  localStorage.setItem(STORAGE_KEY, lang);
  applyTranslations(lang);
}

export function initI18n() {
  applyTranslations(getCurrentLang());

  // Délégation d'événements : fonctionne même si le header est réinjecté après coup.
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-lang]');
    if (btn) setLang(btn.getAttribute('data-lang'));
  });
}

// À appeler après toute réinjection dynamique de contenu (ex : header/footer).
export function refreshTranslations() {
  applyTranslations(getCurrentLang());
}
