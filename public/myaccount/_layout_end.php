<?php
/**
 * MyAccount Layout Footer
 * Closes the layout wrapper
 */
?>
    </main>
</div>

<style>
/* Account Layout Styles */
.account-wrapper {
    display: grid;
    grid-template-columns: 280px 1fr;
    min-height: calc(100vh - 80px);
    background: var(--gray-50);
}

.account-sidebar {
    background: var(--white);
    border-right: 1px solid var(--gray-100);
    padding: var(--space-xl);
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 80px;
    height: calc(100vh - 80px);
}

.account-user {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding-bottom: var(--space-xl);
    border-bottom: 1px solid var(--gray-100);
    margin-bottom: var(--space-xl);
}

.account-avatar {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--black);
}

.account-user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.account-username {
    font-weight: 600;
    color: var(--black);
}

.account-email {
    font-size: 0.85rem;
    color: var(--gray-500);
}

.account-nav {
    display: flex;
    flex-direction: column;
    gap: var(--space-xs);
    flex: 1;
}

.account-nav-item {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-md) var(--space-lg);
    color: var(--gray-600);
    text-decoration: none;
    border-radius: var(--radius-md);
    font-weight: 500;
    transition: all var(--duration-fast) var(--ease-out);
}

.account-nav-item:hover {
    background: var(--gray-50);
    color: var(--black);
}

.account-nav-item.active {
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, rgba(212, 175, 55, 0.05) 100%);
    color: var(--black);
    font-weight: 600;
}

.account-nav-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--gold);
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
}

.nav-icon {
    font-size: 1.25rem;
}

.account-nav-footer {
    padding-top: var(--space-xl);
    border-top: 1px solid var(--gray-100);
    margin-top: auto;
}

.account-nav-item.logout {
    color: var(--gray-500);
}

.account-nav-item.logout:hover {
    color: var(--error);
    background: rgba(239, 68, 68, 0.05);
}

.account-content {
    padding: var(--space-2xl);
    max-width: 1200px;
}

.account-page-title {
    font-family: var(--font-serif);
    font-size: 2rem;
    font-weight: 400;
    margin-bottom: var(--space-xl);
}

/* Cards for account sections */
.account-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-100);
    padding: var(--space-xl);
    margin-bottom: var(--space-lg);
}

.account-card-title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: var(--space-lg);
    padding-bottom: var(--space-md);
    border-bottom: 1px solid var(--gray-100);
}

/* Responsive */
@media (max-width: 1024px) {
    .account-wrapper {
        grid-template-columns: 1fr;
    }
    
    .account-sidebar {
        position: relative;
        top: 0;
        height: auto;
        border-right: none;
        border-bottom: 1px solid var(--gray-100);
    }
    
    .account-nav {
        flex-direction: row;
        flex-wrap: wrap;
        gap: var(--space-sm);
    }
    
    .account-nav-item {
        padding: var(--space-sm) var(--space-md);
        font-size: 0.9rem;
    }
    
    .account-nav-footer {
        display: none;
    }
}

@media (max-width: 640px) {
    .account-user {
        flex-direction: column;
        text-align: center;
    }
    
    .account-nav-item span:not(.nav-icon) {
        display: none;
    }
    
    .account-nav-item {
        padding: var(--space-md);
    }
}
</style>

<?php require_once dirname(__DIR__) . "/components/footer.php"; ?>
