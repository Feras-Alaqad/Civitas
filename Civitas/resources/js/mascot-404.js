const IDLE_TIMEOUT_MS = 2000;
const SEEK_EPSILON = 0.03;
const LERP_RATE = 3.5;
const H_DEAD_ZONE = 0.12;

const POSE = {
    center: 0,
    direction: 0.25, // right (right video) / left (left video) extremum
    up: 0.5,
    down: 0.75,
};

// Map a 2D pointer position onto the active video's 1D timeline.
//   - Horizontal distance from the screen center begs the character toward the
//     direction (right/left) shape; a dead zone near the middle keeps it
//     looking straight ahead ("forward").
//   - Vertical displacement blends between that and the up / down shapes.
//
//   nx, ny: normalized pointer position in [-1, 1].
//   ny =  0 (vertical middle)        -> forward (or direction, if to a side)
//   ny = -1 (screen top)             -> up
//   ny = +1 (screen bottom)          -> down
function pointerToPose(nx, ny) {
    const hDist = Math.abs(nx);
    let strength = 0;
    if (hDist > H_DEAD_ZONE) {
        strength = Math.min(1, (hDist - H_DEAD_ZONE) / (1 - H_DEAD_ZONE));
    }
    const base = POSE.direction * strength * strength;

    const vertical = Math.abs(ny);
    if (ny < 0) {
        return base + (POSE.up - base) * Math.min(1, vertical);
    }
    return base + (POSE.down - base) * Math.min(1, vertical);
}

export function initFullScreenMouseTrackedMascot(container) {
    if (!container || container.dataset.mascot404 === '1') {
        return null;
    }
    container.dataset.mascot404 = '1';

    const videoEls = Array.from(container.querySelectorAll('video[data-mascot-side]'));
    if (videoEls.length === 0) {
        return null;
    }

    const rightEl = videoEls.find((v) => v.dataset.mascotSide === 'right');
    const leftEl = videoEls.find((v) => v.dataset.mascotSide === 'left');

    const makeTracked = (el, side) => {
        const tracked = {
            el,
            side,
            targetTime: 0,
            pendingSeek: false,
            duration: Number(el.duration) || 5,
        };

        el.addEventListener('loadedmetadata', () => {
            if (Number.isFinite(el.duration) && el.duration > 0) {
                tracked.duration = el.duration;
            }
        });

        el.addEventListener('seeked', () => {
            tracked.pendingSeek = false;
        });

        el.pause();
        el.currentTime = 0;

        return tracked;
    };

    const videos = [];
    if (rightEl) {
        videos.push(makeTracked(rightEl, 'right'));
    }
    if (leftEl) {
        videos.push(makeTracked(leftEl, 'left'));
    }

    const getActive = (side) => videos.find((v) => v.side === side) || videos[0];

    let activeSide = 'right';
    let reducedMotion = false;
    let running = false;
    let rafId = null;
    let last = performance.now();
    let idleTimer = null;

    const setInitialOpacity = () => {
        videos.forEach((v) => {
            const visible = v.side === (reducedMotion ? 'right' : activeSide);
            v.el.style.opacity = visible ? '1' : '0';
        });
    };

    const setActive = (side) => {
        if (reducedMotion || side === activeSide) {
            return;
        }

        // Align the incoming video's playhead to the outgoing one so the
        // opacity crossfade shows a continuous motion instead of a jump.
        const from = getActive(activeSide);
        const to = getActive(side);
        if (to && from && to !== from) {
            const fromTime = Number.isFinite(from.el.currentTime) ? from.el.currentTime : 0;
            const maxTime = Math.max(0, to.duration - 0.05);
            try {
                to.el.currentTime = Math.max(0, Math.min(maxTime, fromTime));
            } catch (err) {
                // ignore
            }
        }

        activeSide = side;
        videos.forEach((v) => {
            v.el.style.opacity = v.side === side ? '1' : '0';
        });
    };

    const resetIdle = () => {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(() => {
            getActive(activeSide).targetTime = POSE.center;
        }, IDLE_TIMEOUT_MS);
    };

    const onPointer = (cx, cy) => {
        if (reducedMotion) {
            return;
        }

        const width = window.innerWidth || document.documentElement.clientWidth;
        const height = window.innerHeight || document.documentElement.clientHeight;
        if (!width || !height) {
            return;
        }

        const nx = (cx / width) * 2 - 1;   // -1 (left) .. 1 (right)
        const ny = (cy / height) * 2 - 1;  // -1 (top) .. 1 (bottom)

        // Horizontal side picks the active video (mirrored footage).
        const side = nx >= 0 ? 'right' : 'left';

        setActive(side);

        const tracked = getActive(side);
        const fraction = Math.max(0, Math.min(0.75, pointerToPose(nx, ny)));
        tracked.targetTime = fraction * tracked.duration;

        resetIdle();
    };

    const onMouseMove = (e) => onPointer(e.clientX, e.clientY);

    const onTouchMove = (e) => {
        const touch = e.touches[0];
        if (touch) {
            onPointer(touch.clientX, touch.clientY);
        }
    };

    const onViewportChange = () => {
        if (reducedMotion) {
            return;
        }
        resetIdle();
    };

    window.addEventListener('mousemove', onMouseMove, { passive: true });
    window.addEventListener('touchmove', onTouchMove, { passive: true });
    window.addEventListener('resize', onViewportChange, { passive: true });
    window.addEventListener('orientationchange', onViewportChange);

    function stopLoop() {
        running = false;
        if (rafId) {
            cancelAnimationFrame(rafId);
            rafId = null;
        }
    }

    function tick(now) {
        if (!running) {
            return;
        }

        const dt = Math.min(0.1, (now - last) / 1000);
        last = now;

        // Only the active (visible) video is scrubbed; the hidden one is left
        // untouched.
        const tracked = getActive(activeSide);
        const current = Number.isFinite(tracked.el.currentTime) ? tracked.el.currentTime : 0;
        let desired = current + (tracked.targetTime - current) * (1 - Math.exp(-LERP_RATE * dt));
        desired = Math.max(0, Math.min(tracked.duration - 0.05, desired));

        if (!tracked.pendingSeek && Math.abs(desired - current) > SEEK_EPSILON) {
            tracked.pendingSeek = true;
            try {
                tracked.el.currentTime = desired;
            } catch (err) {
                tracked.pendingSeek = false;
            }
        }

        rafId = requestAnimationFrame(tick);
    }

    function startLoop() {
        if (!running) {
            running = true;
            last = performance.now();
            rafId = requestAnimationFrame(tick);
        }
    }

    const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

    const applyReducedMotion = () => {
        if (reduceMotionQuery.matches) {
            reducedMotion = true;
            stopLoop();

            videos.forEach((v) => v.el.pause());
            if (videos[0]) {
                videos[0].el.currentTime = POSE.center;
            }
            setInitialOpacity();
            return;
        }

        reducedMotion = false;
        setInitialOpacity();
        startLoop();
        resetIdle();
    };

    const start = () => {
        videos.forEach((v) => {
            v.el.pause();
            v.el.currentTime = POSE.center;
        });

        setInitialOpacity();
        resetIdle();

        if (typeof reduceMotionQuery.addEventListener === 'function') {
            reduceMotionQuery.addEventListener('change', applyReducedMotion);
        } else if (typeof reduceMotionQuery.addListener === 'function') {
            reduceMotionQuery.addListener(applyReducedMotion);
        }

        applyReducedMotion();
    };

    const destroy = () => {
        stopLoop();
        clearTimeout(idleTimer);
        window.removeEventListener('mousemove', onMouseMove);
        window.removeEventListener('touchmove', onTouchMove);
        window.removeEventListener('resize', onViewportChange);
        window.removeEventListener('orientationchange', onViewportChange);
        if (typeof reduceMotionQuery.removeEventListener === 'function') {
            reduceMotionQuery.removeEventListener('change', applyReducedMotion);
        } else if (typeof reduceMotionQuery.removeListener === 'function') {
            reduceMotionQuery.removeListener(applyReducedMotion);
        }
    };

    start();

    return { start, destroy };
}

window.initFullScreenMouseTrackedMascot = initFullScreenMouseTrackedMascot;

function boot() {
    const container = document.querySelector('[data-mascot-404]');
    initFullScreenMouseTrackedMascot(container);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}