import { PermissionType } from 'flarum/admin/components/PermissionGrid';

// Custom permission category not in core's PermissionType union; the
// cast keeps the registerPermission() calls type-safe without widening core.
export const BLOG_PERMISSION_CATEGORY = 'blog' as unknown as PermissionType;
