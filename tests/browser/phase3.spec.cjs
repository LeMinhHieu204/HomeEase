const { test, expect } = require('@playwright/test');

const baseURL = process.env.HOMEEASE_BASE_URL || 'http://127.0.0.1:8012';
const viewports = [375, 390, 768, 1024, 1440, 1920];
const pages = [
    ['homepage', '/'],
    ['kitchen category', '/kitchen'],
    ['blog listing', '/blog'],
];

test.describe('HomeEase Phase 3 responsive smoke', () => {
    test.describe.configure({ mode: 'serial' });

    for (const [pageName, path] of pages) {
        for (const width of viewports) {
            test(`${pageName} has no horizontal overflow at ${width}px`, async ({ page }) => {
                await page.setViewportSize({ width, height: 1000 });
                await page.goto(`${baseURL}${path}`, { waitUntil: 'domcontentloaded' });

                await expect(page.locator('body')).toBeVisible();
                await expect.poll(async () => page.evaluate(() => ({
                    scrollWidth: document.documentElement.scrollWidth,
                    viewportWidth: window.innerWidth,
                }))).toEqual({ scrollWidth: width, viewportWidth: width });
            });
        }
    }

    test('category pagination keeps summary and controls horizontal', async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 1000 });
        await page.goto(`${baseURL}/kitchen?page=2`, { waitUntil: 'domcontentloaded' });

        const summary = page.locator('.pagination nav p');
        const controls = page.locator('.pagination nav > div:last-child > div:last-child');

        await expect(summary).toHaveText(/Showing\s+13\s+to\s+17\s+of\s+17\s+results/);
        await expect(summary).toBeVisible();
        await expect(controls).toBeVisible();
        await expect.poll(async () => summary.evaluate((element) => element.getBoundingClientRect().height)).toBeLessThan(30);
        await expect.poll(async () => controls.evaluate((element) => element.getBoundingClientRect().height)).toBeLessThan(50);
    });
});
