export const initLikeButtons = () => {
    document.querySelectorAll('.post-like-btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            const target = e.currentTarget;
            const countEl = target.querySelector('.like-count');
            const liked = target.dataset.liked === '1';
            const postId = Number.parseInt(target.dataset.postId, 10);

            if (!postId) return;
            if (target.dataset.loading === '1') return;
            target.dataset.loading = '1';

            const nextLike = liked ? 0 : 1;
            const prevCount = Number.parseInt(countEl?.textContent ?? '0', 10) || 0;

            // оптимистичное обновление интерфейса
            if (countEl) countEl.textContent = `${prevCount + (nextLike === 1 ? 1 : -1)}`;
            target.dataset.liked = `${nextLike}`;
            target.style.color = nextLike === 1 ? '#0a66c2' : '';

            const body = new URLSearchParams({
                post_id: `${postId}`,
                like: `${nextLike}`
            });

            fetch('process_like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
                credentials: 'same-origin'
            })
                .then((res) => res.json())
                .then((data) => {
                    if (!data?.ok) {
                        throw new Error(data?.error || 'Like failed');
                    }
                    if (countEl) countEl.textContent = `${data.likes}`;
                })
                .catch(() => {
                    // откатываем изменения интерфейса
                    if (countEl) countEl.textContent = `${prevCount}`;
                    target.dataset.liked = liked ? '1' : '0';
                    target.style.color = liked ? '#0a66c2' : '';
                })
                .finally(() => {
                    target.dataset.loading = '0';
                });
        });
    });
};
