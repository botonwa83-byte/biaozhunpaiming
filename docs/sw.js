const REPO_NAME = 'biaozhunpaiming';

self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  
  if (url.pathname.startsWith(`/${REPO_NAME}/`)) {
    return;
  }
  
  if (url.pathname.startsWith('/static/') || url.pathname.startsWith('/uploads/')) {
    event.respondWith(
      fetch(event.request).catch(() => {
        const newUrl = `${url.origin}/${REPO_NAME}${url.pathname}`;
        return fetch(newUrl);
      })
    );
  }
});
