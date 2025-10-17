# GitHub Actions Workflow Syntax - Coding Standards & Best Practices

## File Structure and Location

1. Workflow files MUST use YAML syntax with either `.yml` or `.yaml` file extension
2. Workflow files MUST be stored in the `.github/workflows` directory of the repository
3. Workflow files MUST be located at the repository path: `.github/workflows/{filename}.yml`

## Workflow Configuration

### Name and Run-Name

4. Use `name` to define the workflow name displayed in GitHub UI
5. Use `run-name` to define custom workflow run names with expressions
6. `run-name` can reference `github` and `inputs` contexts

### Trigger Events (on)

7. MUST define at least one trigger event using the `on` keyword
8. Can define single event: `on: push` or multiple events: `on: [push, fork]`
9. When specifying activity types or filters, MUST append colon (`:`) to all events
10. Activity types MUST be defined using `on.<event_name>.types` as an array

### Branch and Tag Filters

11. Use `branches` filter to include branch patterns; use `branches-ignore` to exclude patterns
12. CANNOT use both `branches` and `branches-ignore` for the same event
13. For `push` events, use `tags` filter to include tag patterns; use `tags-ignore` to exclude patterns
14. CANNOT use both `tags` and `tags-ignore` for the same event
15. Glob pattern special characters (`*`, `**`, `+`, `?`, `!`) MUST be escaped with `\` for literal matches
16. Patterns starting with `!` MUST define at least one pattern without `!`
17. Pattern order matters: negative pattern after positive match excludes; positive after negative includes

### Path Filters

18. Use `paths` filter to include file path patterns; use `paths-ignore` to exclude patterns
19. CANNOT use both `paths` and `paths-ignore` for the same event
20. Path patterns MUST match the whole path starting from repository root
21. To include and exclude paths for single event, use `paths` filter with `!` prefix for exclusions
22. Pattern order matters: negative pattern after positive match excludes the path
23. Diffs are limited to 300 files; create specific filters if needed

### Schedule

24. Use POSIX cron syntax for scheduled workflows
25. Scheduled workflows run on the latest commit of the default branch
26. Minimum schedule interval is once every 5 minutes
27. Cron syntax MUST have five fields: minute, hour, day of month, month, day of week
28. Access specific schedule event using `github.event.schedule` context

### Workflow Call

29. Use `on.workflow_call` to define inputs and outputs for reusable workflows
30. Input type parameter is REQUIRED and MUST be one of: `boolean`, `number`, or `string`
31. Use `inputs` context to reference inputs in called workflow
32. If caller passes input not specified in called workflow, an error results
33. Output value MUST be set to output from a job within the called workflow
34. Secrets passed to nested reusable workflow MUST use `jobs.<job_id>.secrets` again

### Workflow Dispatch

35. Use `on.workflow_dispatch` to define manual workflow triggers
36. Maximum number of top-level properties for inputs is 10
37. Maximum payload for inputs is 65,535 characters
38. Input type MUST be one of: `boolean`, `choice`, `number`, `environment`, or `string`

## Permissions

39. Use `permissions` at workflow level to apply to all jobs or at job level for specific jobs
40. Permission values MUST be: `read`, `write`, or `none` (`write` includes `read`)
41. If any permission is specified, all unspecified permissions are set to `none`
42. Use `permissions: read-all` or `permissions: write-all` for all permissions
43. Use `permissions: {}` to disable all permissions
44. CANNOT grant write access to forked repositories (except with specific admin setting)
45. Workflow runs from forked repositories use read-only `GITHUB_TOKEN`

## Environment Variables

46. Use `env` at workflow level for variables available to all jobs
47. Environment variables CANNOT be defined in terms of other variables in the same map
48. More specific environment variables override less specific ones (step > job > workflow)

## Defaults

49. Use `defaults.run` to set default `shell` and `working-directory` for all run steps
50. More specific default settings override less specific ones (job > workflow)
51. CANNOT use contexts or expressions in `defaults.run` at workflow level
52. Supported shell values: `bash`, `pwsh`, `python`, `sh`, `cmd`, `powershell`

## Concurrency

53. Use `concurrency` to ensure only one job/workflow runs at a time in same group
54. Concurrency group can be any string or expression
55. Expression can only use `github`, `inputs`, and `vars` contexts
56. Set `cancel-in-progress: true` to cancel currently running jobs in same group
57. Concurrency group names are case insensitive (`prod` = `Prod`)
58. Ordering is NOT guaranteed for jobs in same concurrency group

## Jobs

### Job Configuration

59. Job ID MUST start with letter or `_` and contain only alphanumeric characters, `-`, or `_`
60. Use `jobs.<job_id>.name` to set display name in GitHub UI
61. Use `jobs.<job_id>.needs` to define job dependencies
62. If dependent job fails/skips, all dependent jobs are skipped unless using conditional
63. Use `always()` conditional to run job even if dependencies fail
64. Jobs run in parallel by default unless dependencies are defined

### Job Conditionals

65. Use `jobs.<job_id>.if` to prevent job from running unless condition is met
66. Can optionally omit `${{ }}` expression syntax in `if` conditionals
67. MUST use `${{ }}` or escape when expression starts with `!`

### Job Runners

68. Use `jobs.<job_id>.runs-on` to define machine type for job
69. Can specify: single string, variable, array of strings, or key-value pair with `group`/`labels`
70. Array of labels requires runner matching ALL specified labels
71. Standard GitHub-hosted runner labels: `ubuntu-latest`, `windows-latest`, `macos-latest`
72. Self-hosted runners MUST include `self-hosted` label first in array
73. `-latest` runner images are latest stable from GitHub, not OS vendor

### Job Environments

74. Use `jobs.<job_id>.environment` to define environment reference
75. Environment can be name only or object with `name` and `url`
76. All deployment protection rules MUST pass before job runs
77. URL value can be expression with allowed contexts: `github`, `inputs`, `vars`, `needs`, `strategy`, `matrix`, `job`, `runner`, `env`, `steps`

### Job Outputs

78. Use `jobs.<job_id>.outputs` to create output map available to dependent jobs
79. Output size maximum is 1 MB per job
80. Total workflow outputs maximum is 50 MB
81. Job outputs are evaluated on runner at end of each job
82. Use `needs` context to reference job outputs in dependent jobs
83. Matrix jobs combine outputs from all jobs in matrix

### Job Timeouts

84. Default job timeout is 360 minutes
85. Use `jobs.<job_id>.timeout-minutes` to set maximum job execution time
86. `GITHUB_TOKEN` expires after 24 hours maximum

## Job Strategy Matrix

87. Use `jobs.<job_id>.strategy.matrix` to define matrix configurations
88. Matrix generates maximum 256 jobs per workflow run
89. Variables become properties in `matrix` context
90. GitHub maximizes parallel jobs by default based on runner availability
91. First variable defined creates first job in workflow run
92. Use `include` to add configurations to matrix combinations
93. Use `exclude` to remove specific combinations (processed before `include`)
94. Excluded configuration only needs partial match to be excluded
95. Matrix variable can be array of objects (not just strings)
96. Use `strategy.fail-fast: true` to cancel all jobs if any fail (default behavior)
97. Use `jobs.<job_id>.continue-on-error: true` to allow specific jobs to fail without failing workflow
98. Use `strategy.max-parallel` to limit concurrent jobs

## Job Steps

99. Steps run in sequence within a job
100. Each step runs in its own process
101. GitHub displays first 1,000 checks maximum
102. Use `jobs.<job_id>.steps[*].id` to set unique identifier for step
103. Use `jobs.<job_id>.steps[*].name` to set display name
104. Use `jobs.<job_id>.steps[*].if` to conditionally run steps
105. Can optionally omit `${{ }}` in step `if` conditionals except when starting with `!`

### Step Actions

106. Use `jobs.<job_id>.steps[*].uses` to select action to run
107. MUST include version by specifying Git ref, SHA, or Docker tag
108. Using commit SHA is safest for stability and security
109. Action syntax formats: `{owner}/{repo}@{ref}`, `./path/to/dir`, `docker://{image}:{tag}`
110. For same repository actions, path is relative to `github.workspace`
111. MUST checkout repository before using local action
112. For private repository actions, generate personal access token and add as secret

### Step Commands

113. Use `jobs.<job_id>.steps[*].run` to execute command-line programs
114. Command maximum length is 21,000 characters
115. Commands run using non-login shells by default
116. Each `run` keyword represents new process and shell
117. Multi-line commands run in same shell

### Step Shell

118. Use `jobs.<job_id>.steps[*].shell` to override default shell
119. Default shell on non-Windows is bash; on Windows is pwsh
120. Custom shell syntax: `command [options] {0} [more_options]`
121. Bash uses `set -e` for fail-fast behavior
122. Bash on specified explicitly uses `-o pipefail` for early pipeline exit
123. PowerShell prepends `$ErrorActionPreference = 'stop'` to scripts

### Step Inputs

124. Use `jobs.<job_id>.steps[*].with` to define action input parameters
125. Input parameters set as environment variables prefixed with `INPUT_` in uppercase
126. Docker container inputs MUST use `args` instead of `with`
127. Use `with.args` to define Docker container inputs as string (not array)
128. Use `with.entrypoint` to override Docker ENTRYPOINT

### Step Environment

129. Use `jobs.<job_id>.steps[*].env` to set step-specific environment variables
130. Step environment variables override job and workflow variables
131. Secrets MUST be set using `secrets` context, not directly in `env`
132. Secrets CANNOT be referenced directly in `if` conditionals

### Step Configuration

133. Use `jobs.<job_id>.steps[*].continue-on-error: true` to allow step to fail without failing job
134. Use `jobs.<job_id>.steps[*].timeout-minutes` to set maximum step execution time
135. Step timeout maximum is 360 minutes for both GitHub-hosted and self-hosted runners
136. Fractional timeout values are NOT supported
137. Use `jobs.<job_id>.steps[*].working-directory` to specify command execution directory

## Containers

138. Container workflows MUST use Linux runners (Ubuntu for GitHub-hosted)
139. Self-hosted runners MUST have Docker installed for containers
140. Use `jobs.<job_id>.container` to run job steps in container
141. Default shell inside container is `sh` (not `bash`)
142. Use `jobs.<job_id>.container.image` to define Docker image
143. Use `jobs.<job_id>.container.credentials` for registry authentication
144. Credentials require `username` and `password` map
145. Use `jobs.<job_id>.container.env` to set container environment variables
146. Use `jobs.<job_id>.container.ports` to expose container ports as array
147. Use `jobs.<job_id>.container.volumes` to set volume mounts
148. Volume syntax: `<source>:<destinationPath>` with absolute paths
149. Use `jobs.<job_id>.container.options` for additional Docker options
150. The `--network` and `--entrypoint` options are NOT supported

## Service Containers

151. Use `jobs.<job_id>.services` to host service containers
152. Service containers MUST use Linux runners
153. Runner automatically creates Docker network and manages container lifecycle
154. Jobs running in containers can reference services by hostname (automatically mapped)
155. Jobs on runner machine MUST map service ports to Docker host
156. Use `${{job.services.<service_name>.ports}}` context to access assigned ports
157. Use `jobs.<job_id>.services.<service_id>.image` to define service image
158. Empty image string prevents service from starting
159. Service credentials syntax same as job container credentials
160. Use `jobs.<job_id>.services.<service_id>.env` for service environment variables
161. Use `jobs.<job_id>.services.<service_id>.ports` to expose service ports
162. Use `jobs.<job_id>.services.<service_id>.volumes` for service volume mounts
163. Use `jobs.<job_id>.services.<service_id>.options` for additional Docker options

## Reusable Workflows

164. Use `jobs.<job_id>.uses` to call reusable workflow
165. Syntax options: `{owner}/{repo}/.github/workflows/{filename}@{ref}` or `./.github/workflows/{filename}`
166. Commit SHA is safest option for stability and security
167. Second syntax option (without owner/repo) uses same commit as caller
168. Ref prefixes (`refs/heads`, `refs/tags`) are NOT allowed
169. CANNOT use contexts or expressions in `uses` keyword
170. Use `jobs.<job_id>.with` to pass inputs to called workflow
171. Input identifiers MUST match names in called workflow
172. Inputs NOT available as environment variables (use `inputs` context instead)
173. Use `jobs.<job_id>.secrets` to pass secrets to called workflow
174. Secret names MUST match names defined in called workflow
175. Use `jobs.<job_id>.secrets.inherit` to pass all calling workflow secrets
176. Inherit keyword works across repositories in same org or same enterprise

## Filter Patterns

177. `*` matches zero or more characters but NOT slash `/`
178. `**` matches zero or more of any character including slash
179. `?` matches zero or one of preceding character
180. `+` matches one or more of preceding character
181. `[]` matches one alphanumeric character from list or range
182. `!` at pattern start negates previous positive patterns
183. Patterns starting with `*`, `[`, or `!` MUST be enclosed in quotes
184. Flow sequences with `[` and/or `]` MUST be enclosed in quotes

## Workflow Best Practices

185. Workflow files are visible to all repository collaborators
186. GitHub automatically evaluates `if` conditionals as expressions
187. Use semantic versioning for reusable workflow versions
188. Default branch triggers receive events when workflow file is on default branch
189. Pull requests use three-dot diffs; pushes use two-dot diffs
190. More than 1,000 commits or timeout causes workflow to always run
191. Skipped workflows due to filtering remain in "Pending" state
192. Matrix job output order is NOT guaranteed
193. Use unique output names in matrix to avoid overwriting
194. `GITHUB_TOKEN` permissions are calculated: enterprise/org/repo defaults → workflow level → job level
195. Dependabot pull requests use read-only `GITHUB_TOKEN`
